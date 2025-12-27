<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Repository\MachineRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/machines')]
class MachineController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MachineRepository $machineRepository,
        private ClientRepository $clientRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_machines_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');

        $qb = $this->machineRepository->createQueryBuilder('m')
            ->leftJoin('m.client', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('m.reference LIKE :search OR m.modele LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('m.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $machines = $qb->getQuery()->getResult();
        $data = $this->serializer->serialize($machines, 'json', ['groups' => 'machine:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_machines_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($machine, 'json', ['groups' => 'machine:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_machines_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $machine = new Machine();
        $machine->setReference($data['reference'] ?? '');
        $machine->setModele($data['modele'] ?? '');
        $machine->setMarque($data['marque'] ?? '');
        $machine->setType($data['type'] ?? '');
        $machine->setDateAcquisition(new \DateTime($data['dateAcquisition'] ?? 'now'));
        $machine->setStatut($data['statut'] ?? 'En service');

        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            if ($client) {
                $machine->setClient($client);
            }
        }

        $errors = $this->validator->validate($machine);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($machine);
        $this->em->flush();

        $responseData = $this->serializer->serialize($machine, 'json', ['groups' => 'machine:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_machines_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['reference']))
            $machine->setReference($data['reference']);
        if (isset($data['modele']))
            $machine->setModele($data['modele']);
        if (isset($data['marque']))
            $machine->setMarque($data['marque']);
        if (isset($data['type']))
            $machine->setType($data['type']);
        if (isset($data['dateAcquisition']))
            $machine->setDateAcquisition(new \DateTime($data['dateAcquisition']));
        if (isset($data['statut']))
            $machine->setStatut($data['statut']);
        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            $machine->setClient($client);
        }

        $errors = $this->validator->validate($machine);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($machine, 'json', ['groups' => 'machine:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_machines_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($machine);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/interventions', name: 'api_machines_interventions', methods: ['GET'])]
    public function getInterventions(int $id): JsonResponse
    {
        $machine = $this->machineRepository->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $interventions = $machine->getInterventions();
        $data = $this->serializer->serialize($interventions, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
