<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Entity\MouvementStock;
use App\Repository\PieceRepository;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/pieces')]
class PieceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PieceRepository $pieceRepository,
        private FournisseurRepository $fournisseurRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_pieces_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $search = $request->query->get('search');

        if ($search) {
            $pieces = $this->pieceRepository->search($search);
        } else {
            $pieces = $this->pieceRepository->findAll();
        }

        $data = $this->serializer->serialize($pieces, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/low-stock', name: 'api_pieces_low_stock', methods: ['GET'])]
    public function lowStock(): JsonResponse
    {
        $pieces = $this->pieceRepository->findLowStock();
        $data = $this->serializer->serialize($pieces, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_pieces_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $piece = $this->pieceRepository->find($id);
        if (!$piece) {
            return $this->json(['error' => 'Piece not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($piece, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_pieces_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $piece = new Piece();
        $piece->setReference($data['reference'] ?? '');
        $piece->setNom($data['nom'] ?? '');
        $piece->setDescription($data['description'] ?? null);
        $piece->setPrixUnitaire($data['prixUnitaire'] ?? 0);
        $piece->setQuantiteStock($data['quantiteStock'] ?? 0);
        $piece->setSeuilAlerte($data['seuilAlerte'] ?? 5);
        $piece->setEmplacement($data['emplacement'] ?? null);

        // Set fournisseur if provided
        if (isset($data['fournisseurId'])) {
            $fournisseur = $this->fournisseurRepository->find($data['fournisseurId']);
            if ($fournisseur) {
                $piece->setFournisseur($fournisseur);
            }
        }

        $errors = $this->validator->validate($piece);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($piece);

        // Create initial stock movement if stock > 0
        if ($piece->getQuantiteStock() > 0) {
            $mouvement = MouvementStock::create(
                $piece,
                MouvementStock::TYPE_ENTREE,
                $piece->getQuantiteStock(),
                'Stock initial'
            );
            // Override quantiteAvant since it's new
            $mouvement->setQuantiteAvant(0);
            $this->em->persist($mouvement);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($piece, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_pieces_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $piece = $this->pieceRepository->find($id);
        if (!$piece) {
            return $this->json(['error' => 'Piece not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['reference']))
            $piece->setReference($data['reference']);
        if (isset($data['nom']))
            $piece->setNom($data['nom']);
        if (isset($data['description']))
            $piece->setDescription($data['description']);
        if (isset($data['prixUnitaire']))
            $piece->setPrixUnitaire($data['prixUnitaire']);
        if (isset($data['seuilAlerte']))
            $piece->setSeuilAlerte($data['seuilAlerte']);
        if (isset($data['emplacement']))
            $piece->setEmplacement($data['emplacement']);

        // Update fournisseur if provided
        if (isset($data['fournisseurId'])) {
            $fournisseur = $this->fournisseurRepository->find($data['fournisseurId']);
            $piece->setFournisseur($fournisseur);
        }

        $errors = $this->validator->validate($piece);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($piece, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/stock', name: 'api_pieces_adjust_stock', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function adjustStock(int $id, Request $request): JsonResponse
    {
        $piece = $this->pieceRepository->find($id);
        if (!$piece) {
            return $this->json(['error' => 'Piece not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null; // 'entree', 'sortie', 'ajustement'
        $quantite = (int) ($data['quantite'] ?? 0);
        $motif = $data['motif'] ?? null;

        if (!in_array($type, [MouvementStock::TYPE_ENTREE, MouvementStock::TYPE_SORTIE, MouvementStock::TYPE_AJUSTEMENT])) {
            return $this->json(['error' => 'Invalid type (use: entree, sortie, ajustement)'], Response::HTTP_BAD_REQUEST);
        }

        if ($quantite <= 0 && $type !== MouvementStock::TYPE_AJUSTEMENT) {
            return $this->json(['error' => 'Quantity must be positive'], Response::HTTP_BAD_REQUEST);
        }

        // Create movement record
        $mouvement = MouvementStock::create($piece, $type, $quantite, $motif);
        $this->em->persist($mouvement);

        // Update stock based on type
        try {
            match ($type) {
                MouvementStock::TYPE_ENTREE => $piece->ajouterStock($quantite),
                MouvementStock::TYPE_SORTIE => $piece->deduireStock($quantite),
                MouvementStock::TYPE_AJUSTEMENT => $piece->setQuantiteStock($quantite),
            };
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($piece, 'json', ['groups' => 'piece:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_pieces_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $piece = $this->pieceRepository->find($id);
        if (!$piece) {
            return $this->json(['error' => 'Piece not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if piece has been used in interventions
        if (!$piece->getPieceInterventions()->isEmpty()) {
            return $this->json(
                ['error' => 'Cannot delete part that has been used in interventions'],
                Response::HTTP_CONFLICT
            );
        }

        $this->em->remove($piece);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
