<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Panne;
use App\Repository\InterventionRepository;
use App\Repository\MachineRepository;
use App\Repository\PanneRepository;
use App\Repository\TechnicienRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/interventions')]
class InterventionController extends AbstractController
{
    private const VALID_STATUSES = ['En attente', 'En cours', 'Terminee', 'Annulee'];
    private const STATUS_TRANSITIONS = [
        'En attente' => ['En cours', 'Annulee'],
        'En cours' => ['Terminee', 'Annulee'],
        'Terminee' => [],
        'Annulee' => [],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private InterventionRepository $interventionRepository,
        private MachineRepository $machineRepository,
        private TechnicienRepository $technicienRepository,
        private PanneRepository $panneRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_interventions_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $params = [
            'page' => $request->query->getInt('page', 1),
            'limit' => $request->query->getInt('limit', 10),
            'search' => $request->query->get('search'),
            'statut' => $request->query->get('statut'),
            'priorite' => $request->query->get('priorite'),
            'technicien' => $request->query->getInt('technicien')
        ];

        $result = $this->interventionRepository->findBySearch($params);
        $data = $this->serializer->serialize($result, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_interventions_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_interventions_create', methods: ['POST'])]
    #[IsGranted('ROLE_RECEPTIONIST')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['machineId'])) {
            return $this->json(['error' => 'machineId is required'], Response::HTTP_BAD_REQUEST);
        }

        $machine = $this->machineRepository->find($data['machineId']);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $intervention = new Intervention();
        $intervention->setMachine($machine);
        $intervention->setType($data['type'] ?? 'corrective');
        $intervention->setPriorite($data['priorite'] ?? 'Normale');
        $intervention->setStatut('En attente');
        $intervention->setDateDebut(new \DateTime($data['dateDebut'] ?? 'now'));
        $intervention->setDescription($data['description'] ?? null);

        if (isset($data['dateFinPrevue'])) {
            $intervention->setDateFinPrevue(new \DateTime($data['dateFinPrevue']));
        }

        if (isset($data['technicienId'])) {
            $technicien = $this->technicienRepository->find($data['technicienId']);
            if ($technicien) {
                $intervention->setTechnicien($technicien);
                // Freeze the technician's hourly rate at creation time
                $intervention->setTauxHoraireApplique($technicien->getTauxHoraire());
            }
        }

        if (isset($data['coutPieces'])) {
            $intervention->setCoutPieces((float) $data['coutPieces']);
        }

        // If corrective intervention, create a linked Panne
        if ($intervention->getType() === 'corrective') {
            $panne = new Panne();
            $panne->setMachine($machine);
            $panne->setDateDeclaration(new \DateTime());
            $panne->setDescription($data['panneDescription'] ?? $data['description'] ?? 'Panne signalée');
            $panne->setGravite($data['panneGravite'] ?? 'Moyenne');
            $panne->setIntervention($intervention); // FIX: Bidirectional link
            $this->em->persist($panne);
        }

        $errors = $this->validator->validate($intervention);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($intervention);
        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_interventions_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['description']))
            $intervention->setDescription($data['description']);
        if (isset($data['resolution']))
            $intervention->setResolution($data['resolution']);
        if (isset($data['priorite']))
            $intervention->setPriorite($data['priorite']);
        if (isset($data['dateFinPrevue']))
            $intervention->setDateFinPrevue(new \DateTime($data['dateFinPrevue']));
        if (isset($data['coutPieces']))
            $intervention->setCoutPieces((float) $data['coutPieces']);

        if (isset($data['technicienId'])) {
            $technicien = $this->technicienRepository->find($data['technicienId']);
            if ($technicien) {
                $intervention->setTechnicien($technicien);
                // Only update frozen rate if not already set
                if ($intervention->getTauxHoraireApplique() === null) {
                    $intervention->setTauxHoraireApplique($technicien->getTauxHoraire());
                }

                // SYNC: Update linked panne status when technician assigned
                $panne = $intervention->getPanne();
                if ($panne && $panne->getStatut() === 'Declaree') {
                    $panne->setStatut('En traitement');
                }
            }
        }

        $errors = $this->validator->validate($intervention);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/status', name: 'api_interventions_status', methods: ['PATCH'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newStatut = $data['statut'] ?? null;

        if (!$newStatut || !in_array($newStatut, self::VALID_STATUSES)) {
            return $this->json(['error' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $currentStatut = $intervention->getStatut();
        $allowedTransitions = self::STATUS_TRANSITIONS[$currentStatut] ?? [];

        if (!in_array($newStatut, $allowedTransitions)) {
            return $this->json([
                'error' => "Cannot transition from '$currentStatut' to '$newStatut'",
                'allowed' => $allowedTransitions
            ], Response::HTTP_BAD_REQUEST);
        }

        $intervention->setStatut($newStatut);
        $machine = $intervention->getMachine();

        // Handle status-specific logic
        switch ($newStatut) {
            case 'En cours':
                // Set machine to "En maintenance"
                $machine->setStatut('En maintenance');
                // Update technician status if assigned
                if ($intervention->getTechnicien()) {
                    $intervention->getTechnicien()->setStatut('En intervention');
                }
                break;

            case 'Terminee':
                // Set end date and calculate costs
                $intervention->setDateFinReelle(new \DateTime());
                $intervention->calculateCosts();

                // Set machine back to "En service"
                $machine->setStatut('En service');

                // Update technician status
                if ($intervention->getTechnicien()) {
                    $intervention->getTechnicien()->setStatut('Disponible');
                }

                // Sync panne status - mark linked panne as resolved
                $linkedPanne = $this->panneRepository->findOneBy(['intervention' => $intervention]);
                if ($linkedPanne && $linkedPanne->getStatut() !== 'Resolue') {
                    $linkedPanne->setStatut('Resolue');
                }
                break;

            case 'Annulee':
                // Check if machine has other active interventions
                $activeInterventions = $this->interventionRepository->createQueryBuilder('i')
                    ->where('i.machine = :machine')
                    ->andWhere('i.statut IN (:statuts)')
                    ->andWhere('i.id != :id')
                    ->setParameter('machine', $machine)
                    ->setParameter('statuts', ['En attente', 'En cours'])
                    ->setParameter('id', $intervention->getId())
                    ->getQuery()
                    ->getResult();

                if (count($activeInterventions) === 0) {
                    $machine->setStatut('En service');
                }

                // Update technician status
                if ($intervention->getTechnicien()) {
                    $intervention->getTechnicien()->setStatut('Disponible');
                }
                break;
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/confirm-tech', name: 'api_interventions_confirm_tech', methods: ['PATCH'])]
    public function confirmTech(int $id): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $intervention->setConfirmationTechnicien(true);
        $intervention->setConfirmationTechnicienAt(new \DateTime());
        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/confirm-client', name: 'api_interventions_confirm_client', methods: ['PATCH'])]
    public function confirmClient(int $id): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $intervention->setConfirmationClient(true);
        $intervention->setConfirmationClientAt(new \DateTime());
        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_interventions_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($intervention);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/logs', name: 'api_interventions_add_log', methods: ['POST'])]
    public function addLog(int $id, Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $log = new \App\Entity\InterventionLog();
        $log->setUser($user);
        $log->setMessage($data['message'] ?? '');
        $log->setType($data['type'] ?? 'comment');

        $intervention->addLog($log);
        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}/sign', name: 'api_interventions_sign', methods: ['POST'])]
    public function sign(int $id, Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return $this->json(['error' => 'Intervention not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $intervention->setSignatureClient($data['signature'] ?? null);
        $intervention->setSignerNom($data['signerNom'] ?? null);
        $intervention->setConfirmationClient(true);
        $intervention->setConfirmationClientAt(new \DateTime());

        $this->em->flush();

        $responseData = $this->serializer->serialize($intervention, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
}

