<?php

namespace App\Controller;

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

#[Route('/api/pannes')]
class PanneController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PanneRepository $panneRepository,
        private MachineRepository $machineRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_pannes_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $pannes = $this->panneRepository->findBy([], ['dateDeclaration' => 'DESC']);
        $data = $this->serializer->serialize($pannes, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/machine/{machineId}', name: 'api_pannes_by_machine', methods: ['GET'])]
    public function getByMachine(int $machineId): JsonResponse
    {
        $machine = $this->machineRepository->find($machineId);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $pannes = $this->panneRepository->findBy(['machine' => $machine], ['dateDeclaration' => 'DESC']);
        $data = $this->serializer->serialize($pannes, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_pannes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['machineId'])) {
            return $this->json(['error' => 'machineId is required'], Response::HTTP_BAD_REQUEST);
        }

        $machine = $this->machineRepository->find($data['machineId']);
        if (!$machine) {
            return $this->json(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        $panne = new Panne();
        $panne->setMachine($machine);
        $panne->setDateDeclaration(new \DateTime($data['dateDeclaration'] ?? 'now'));
        $panne->setDescription($data['description'] ?? '');
        $panne->setGravite($data['gravite'] ?? 'Moyenne');

        $errors = $this->validator->validate($panne);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($panne);
        $this->em->flush();

        $responseData = $this->serializer->serialize($panne, 'json', ['groups' => 'panne:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }
}
