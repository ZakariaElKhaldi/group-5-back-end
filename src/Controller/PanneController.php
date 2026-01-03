<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Panne;
use App\Repository\PanneRepository;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for managing pannes (breakdowns/failures).
 * 
 * Pannes are linked to interventions. When an intervention becomes "Terminee",
 * the linked panne should become "Resolue".
 */
#[Route('/api/pannes')]
class PanneController extends AbstractController
{
    private const VALID_GRAVITE = ['Faible', 'Moyenne', 'Elevee'];
    private const VALID_STATUT = ['Declaree', 'En traitement', 'Resolue'];

    public function __construct(
        private EntityManagerInterface $em,
        private PanneRepository $panneRepository,
        private MachineRepository $machineRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * List all pannes, ordered by date (most recent first)
     */
    #[Route('', name: 'api_pannes_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $statut = $request->query->get('statut', '');
        $gravite = $request->query->get('gravite', '');

        $qb = $this->panneRepository->createQueryBuilder('p')
            ->leftJoin('p.machine', 'm')
            ->leftJoin('p.intervention', 'i')
            ->addSelect('m', 'i')
            ->orderBy('p.dateDeclaration', 'DESC');

        if ($statut && in_array($statut, self::VALID_STATUT)) {
            $qb->andWhere('p.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($gravite && in_array($gravite, self::VALID_GRAVITE)) {
            $qb->andWhere('p.gravite = :gravite')
                ->setParameter('gravite', $gravite);
        }

        $pannes = $qb->getQuery()->getResult();
        $data = $this->serializer->serialize($pannes, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get a single panne by ID
     */
    #[Route('/{id}', name: 'api_pannes_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $panne = $this->panneRepository->find($id);
        if (!$panne) {
            return $this->json(['error' => 'Panne non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($panne, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get pannes for a specific machine
     */
    #[Route('/machine/{machineId}', name: 'api_pannes_by_machine', methods: ['GET'])]
    public function getByMachine(int $machineId): JsonResponse
    {
        $machine = $this->machineRepository->find($machineId);
        if (!$machine) {
            return $this->json(['error' => 'Machine non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $pannes = $this->panneRepository->findBy(['machine' => $machine], ['dateDeclaration' => 'DESC']);
        $data = $this->serializer->serialize($pannes, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new panne
     * 
     * This will automatically create an associated intervention marked as corrective.
     */
    #[Route('', name: 'api_pannes_create', methods: ['POST'])]
    #[IsGranted('ROLE_RECEPTIONIST')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['machineId'])) {
            return $this->json(['error' => 'machineId est requis'], Response::HTTP_BAD_REQUEST);
        }

        $machine = $this->machineRepository->find($data['machineId']);
        if (!$machine) {
            return $this->json(['error' => 'Machine non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Validate gravite
        $gravite = $data['gravite'] ?? 'Moyenne';
        if (!in_array($gravite, self::VALID_GRAVITE)) {
            return $this->json([
                'error' => 'Gravité invalide. Valeurs acceptées: ' . implode(', ', self::VALID_GRAVITE)
            ], Response::HTTP_BAD_REQUEST);
        }

        // Create the Panne
        $panne = new Panne();
        $panne->setMachine($machine);
        $panne->setDateDeclaration(new \DateTime($data['dateDeclaration'] ?? 'now'));
        $panne->setDescription($data['description'] ?? '');
        $panne->setGravite($gravite);
        $panne->setStatut('Declaree'); // Changed from 'En traitement' - will change when tech assigned

        // Update machine status to "En panne"
        $machine->setStatut('En panne');

        // AUTO-CREATE a corrective intervention linked to this Panne
        $intervention = new Intervention();
        $intervention->setMachine($machine);
        $intervention->setType('corrective');
        $intervention->setStatut('En attente');
        $intervention->setDateDebut(new \DateTime());
        $intervention->setDescription($data['description'] ?? 'Panne signalée');

        // Map gravite to priorite
        $prioriteMap = [
            'Faible' => 'Basse',
            'Moyenne' => 'Normale',
            'Elevee' => 'Urgente',
        ];
        $intervention->setPriorite($prioriteMap[$gravite] ?? 'Normale');

        // Link panne to intervention
        $panne->setIntervention($intervention);

        $errors = $this->validator->validate($panne);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($intervention);
        $this->em->persist($panne);
        $this->em->flush();

        $responseData = $this->serializer->serialize($panne, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    /**
     * Update a panne
     * 
     * Only allows updating description, gravite, and statut.
     * If statut is set to "Resolue" and there's a linked intervention, the intervention is also marked as "Terminee".
     * 
     * UPDATED: Technicians can now update pannes linked to their interventions
     */
    #[Route('/{id}', name: 'api_pannes_update', methods: ['PUT'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function update(int $id, Request $request): JsonResponse
    {
        $panne = $this->panneRepository->find($id);
        if (!$panne) {
            return $this->json(['error' => 'Panne non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update description
        if (isset($data['description'])) {
            $panne->setDescription($data['description']);
        }

        // Update gravite
        if (isset($data['gravite'])) {
            if (!in_array($data['gravite'], self::VALID_GRAVITE)) {
                return $this->json([
                    'error' => 'Gravité invalide. Valeurs acceptées: ' . implode(', ', self::VALID_GRAVITE)
                ], Response::HTTP_BAD_REQUEST);
            }
            $panne->setGravite($data['gravite']);
        }

        // Update statut
        if (isset($data['statut'])) {
            if (!in_array($data['statut'], self::VALID_STATUT)) {
                return $this->json([
                    'error' => 'Statut invalide. Valeurs acceptées: ' . implode(', ', self::VALID_STATUT)
                ], Response::HTTP_BAD_REQUEST);
            }

            $oldStatut = $panne->getStatut();
            $newStatut = $data['statut'];
            $panne->setStatut($newStatut);

            // Sync with intervention if resolving the panne
            if ($newStatut === 'Resolue' && $oldStatut !== 'Resolue') {
                $intervention = $panne->getIntervention();
                if ($intervention && !in_array($intervention->getStatut(), ['Terminee', 'Annulee'])) {
                    $intervention->setStatut('Terminee');
                    $intervention->setDateFinReelle(new \DateTime());
                    $intervention->calculateCosts();
                }

                // Update machine status
                $machine = $panne->getMachine();
                if ($machine) {
                    // Check if machine has other unresolved pannes
                    $otherPannes = $this->panneRepository->createQueryBuilder('p')
                        ->where('p.machine = :machine')
                        ->andWhere('p.id != :id')
                        ->andWhere('p.statut != :statut')
                        ->setParameter('machine', $machine)
                        ->setParameter('id', $panne->getId())
                        ->setParameter('statut', 'Resolue')
                        ->getQuery()
                        ->getResult();

                    if (count($otherPannes) === 0) {
                        $machine->setStatut('En service');
                    }
                }
            }
        }

        $errors = $this->validator->validate($panne);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($panne, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     * Delete a panne
     * 
     * Also deletes the linked intervention if it exists and is not in progress.
     */
    #[Route('/{id}', name: 'api_pannes_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $panne = $this->panneRepository->find($id);
        if (!$panne) {
            return $this->json(['error' => 'Panne non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Check if linked intervention is in progress
        $intervention = $panne->getIntervention();
        if ($intervention && $intervention->getStatut() === 'En cours') {
            return $this->json([
                'error' => 'Impossible de supprimer une panne dont l\'intervention est en cours'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Remove linked intervention if exists
        if ($intervention) {
            $this->em->remove($intervention);
        }

        $this->em->remove($panne);
        $this->em->flush();

        return $this->json(['message' => 'Panne supprimée avec succès'], Response::HTTP_OK);
    }
}
