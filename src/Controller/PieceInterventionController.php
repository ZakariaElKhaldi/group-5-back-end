<?php

namespace App\Controller;

use App\Entity\MouvementStock;
use App\Entity\PieceIntervention;
use App\Repository\InterventionRepository;
use App\Repository\PieceInterventionRepository;
use App\Repository\PieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for managing pieces used in interventions.
 * 
 * This controller handles:
 * - Adding pieces to an intervention (with automatic stock deduction)
 * - Removing pieces from an intervention (with stock restoration)
 * - Listing pieces used in an intervention
 */
#[Route('/api/interventions/{interventionId}/pieces')]
class PieceInterventionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private InterventionRepository $interventionRepository,
        private PieceRepository $pieceRepository,
        private PieceInterventionRepository $pieceInterventionRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Get all pieces used in an intervention
     */
    #[Route('', name: 'api_intervention_pieces_index', methods: ['GET'])]
    public function index(int $interventionId): JsonResponse
    {
        $intervention = $this->interventionRepository->find($interventionId);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $piecesUtilisees = $intervention->getPiecesUtilisees();
        $data = $this->serializer->serialize(
            $piecesUtilisees,
            'json',
            ['groups' => 'piece_intervention:read']
        );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Add a piece to an intervention
     * 
     * Expected payload:
     * {
     *   "pieceId": int,
     *   "quantite": int (default: 1)
     * }
     * 
     * This will:
     * 1. Create a PieceIntervention record
     * 2. Deduct stock from the piece
     * 3. Create a MouvementStock record for traceability
     * 4. Recalculate intervention costs
     */
    #[Route('', name: 'api_intervention_pieces_add', methods: ['POST'])]
    public function addPiece(int $interventionId, Request $request): JsonResponse
    {
        // Find intervention
        $intervention = $this->interventionRepository->find($interventionId);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Check intervention status - can only add pieces to active interventions
        if (!in_array($intervention->getStatut(), ['En attente', 'En cours'])) {
            return $this->json([
                'error' => 'Impossible d\'ajouter des pièces à une intervention terminée ou annulée'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['pieceId'])) {
            return $this->json(['error' => 'pieceId est requis'], Response::HTTP_BAD_REQUEST);
        }

        $piece = $this->pieceRepository->find($data['pieceId']);
        if (!$piece) {
            return $this->json(['error' => 'Pièce non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $quantite = isset($data['quantite']) ? (int) $data['quantite'] : 1;
        if ($quantite <= 0) {
            return $this->json(['error' => 'La quantité doit être supérieure à 0'], Response::HTTP_BAD_REQUEST);
        }

        // Check stock availability
        if ($piece->getQuantiteStock() < $quantite) {
            return $this->json([
                'error' => sprintf(
                    'Stock insuffisant pour "%s" (demandé: %d, disponible: %d)',
                    $piece->getNom(),
                    $quantite,
                    $piece->getQuantiteStock()
                )
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if piece already used in this intervention
        $existingUsage = $this->pieceInterventionRepository->findOneBy([
            'piece' => $piece,
            'intervention' => $intervention
        ]);

        if ($existingUsage) {
            // Update existing usage instead of creating duplicate
            $oldQuantite = $existingUsage->getQuantite();
            $additionalQuantite = $quantite;

            // Deduct additional stock
            try {
                $piece->deduireStock($additionalQuantite);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }

            $existingUsage->setQuantite($oldQuantite + $additionalQuantite);

            // Create stock movement record
            $this->createStockMovement($piece, -$additionalQuantite, $intervention, 'Ajout pièce intervention');

        } else {
            // Create new PieceIntervention
            $pieceIntervention = new PieceIntervention();
            $pieceIntervention->setPiece($piece);
            $pieceIntervention->setIntervention($intervention);
            $pieceIntervention->setQuantite($quantite);
            $pieceIntervention->setPrixUnitaireApplique($piece->getPrixUnitaire()); // Freeze current price

            // Deduct stock
            try {
                $piece->deduireStock($quantite);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }

            // Validate
            $errors = $this->validator->validate($pieceIntervention);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $this->em->persist($pieceIntervention);

            // Create stock movement record
            $this->createStockMovement($piece, -$quantite, $intervention, 'Utilisation intervention');
        }

        // Flush to persist the piece intervention first
        $this->em->flush();

        // Now recalculate with accurate DB data and flush again
        $this->recalculateInterventionCost($intervention);
        $this->em->flush();

        // Return updated intervention with pieces
        $responseData = $this->serializer->serialize(
            $intervention,
            'json',
            ['groups' => 'intervention:read']
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    /**
     * Remove a piece from an intervention
     * 
     * This will:
     * 1. Remove the PieceIntervention record
     * 2. Restore stock to the piece
     * 3. Create a MouvementStock record
     * 4. Recalculate intervention costs
     */
    #[Route('/{pieceInterventionId}', name: 'api_intervention_pieces_remove', methods: ['DELETE'])]
    public function removePiece(int $interventionId, int $pieceInterventionId): JsonResponse
    {
        $intervention = $this->interventionRepository->find($interventionId);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Check intervention status
        if (!in_array($intervention->getStatut(), ['En attente', 'En cours'])) {
            return $this->json([
                'error' => 'Impossible de modifier les pièces d\'une intervention terminée ou annulée'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pieceIntervention = $this->pieceInterventionRepository->find($pieceInterventionId);
        if (!$pieceIntervention) {
            return $this->json(['error' => 'Usage de pièce non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Verify it belongs to this intervention
        if ($pieceIntervention->getIntervention()->getId() !== $interventionId) {
            return $this->json(['error' => 'Cette pièce n\'appartient pas à cette intervention'], Response::HTTP_BAD_REQUEST);
        }

        $piece = $pieceIntervention->getPiece();
        $quantite = $pieceIntervention->getQuantite();

        // Restore stock
        $piece->ajouterStock($quantite);

        // Create stock movement record
        $this->createStockMovement($piece, $quantite, $intervention, 'Retour pièce intervention');

        // Remove the piece usage
        $this->em->remove($pieceIntervention);

        // Flush to remove the piece first
        $this->em->flush();

        // Now recalculate with accurate DB data and flush again
        $this->recalculateInterventionCost($intervention);
        $this->em->flush();

        return $this->json(['message' => 'Pièce retirée avec succès'], Response::HTTP_OK);
    }

    /**
     * Update quantity of a piece in an intervention
     */
    #[Route('/{pieceInterventionId}', name: 'api_intervention_pieces_update', methods: ['PATCH'])]
    public function updatePiece(int $interventionId, int $pieceInterventionId, Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($interventionId);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if (!in_array($intervention->getStatut(), ['En attente', 'En cours'])) {
            return $this->json([
                'error' => 'Impossible de modifier les pièces d\'une intervention terminée ou annulée'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pieceIntervention = $this->pieceInterventionRepository->find($pieceInterventionId);
        if (!$pieceIntervention || $pieceIntervention->getIntervention()->getId() !== $interventionId) {
            return $this->json(['error' => 'Usage de pièce non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newQuantite = isset($data['quantite']) ? (int) $data['quantite'] : null;

        if ($newQuantite === null || $newQuantite <= 0) {
            return $this->json(['error' => 'La quantité doit être supérieure à 0'], Response::HTTP_BAD_REQUEST);
        }

        $piece = $pieceIntervention->getPiece();
        $oldQuantite = $pieceIntervention->getQuantite();
        $difference = $newQuantite - $oldQuantite;

        if ($difference > 0) {
            // Need more pieces - check stock
            if ($piece->getQuantiteStock() < $difference) {
                return $this->json([
                    'error' => sprintf(
                        'Stock insuffisant pour "%s" (demandé: %d de plus, disponible: %d)',
                        $piece->getNom(),
                        $difference,
                        $piece->getQuantiteStock()
                    )
                ], Response::HTTP_BAD_REQUEST);
            }
            $piece->deduireStock($difference);
            $this->createStockMovement($piece, -$difference, $intervention, 'Ajustement intervention (+)');
        } elseif ($difference < 0) {
            // Returning pieces to stock
            $piece->ajouterStock(abs($difference));
            $this->createStockMovement($piece, abs($difference), $intervention, 'Ajustement intervention (-)');
        }

        $pieceIntervention->setQuantite($newQuantite);

        // Flush to update the quantity first
        $this->em->flush();

        // Now recalculate with accurate DB data and flush again
        $this->recalculateInterventionCost($intervention);
        $this->em->flush();

        $responseData = $this->serializer->serialize(
            $intervention,
            'json',
            ['groups' => 'intervention:read']
        );

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     * Helper: Create a stock movement record for traceability
     */
    private function createStockMovement($piece, int $quantite, $intervention, string $motif): void
    {
        // Calculate stock before the change (stock has already been modified)
        $stockAfter = $piece->getQuantiteStock();
        $stockBefore = $quantite > 0
            ? $stockAfter - $quantite  // For returns (entree), stock was lower before
            : $stockAfter + abs($quantite);  // For usage (sortie), stock was higher before

        $mouvement = new MouvementStock();
        $mouvement->setPiece($piece);
        $mouvement->setType($quantite > 0 ? 'entree' : 'sortie');
        $mouvement->setQuantite(abs($quantite));
        $mouvement->setQuantiteAvant($stockBefore);
        $mouvement->setQuantiteApres($stockAfter);
        $mouvement->setMotif($motif . ' #' . $intervention->getId());

        $this->em->persist($mouvement);
    }

    /**
     * Helper: Recalculate total parts cost for an intervention using DB query
     * This is called after flush to ensure accurate totals
     */
    private function recalculateInterventionCost($intervention): void
    {
        // Use a fresh query to get accurate totals from the database
        $totalCost = $this->pieceInterventionRepository->createQueryBuilder('pi')
            ->select('SUM(pi.quantite * pi.prixUnitaireApplique)')
            ->where('pi.intervention = :intervention')
            ->setParameter('intervention', $intervention)
            ->getQuery()
            ->getSingleScalarResult();

        $totalCost = (float) ($totalCost ?? 0);
        $intervention->setCoutPieces($totalCost);

        // Also update total cost if labor cost exists
        $coutMainOeuvre = $intervention->getCoutMainOeuvre() ?? 0;
        $intervention->setCoutTotal($coutMainOeuvre + $totalCost);
    }
}
