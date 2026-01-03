<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ClientRepository $clientRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_clients_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $params = [
            'page' => $request->query->getInt('page', 1),
            'limit' => $request->query->getInt('limit', 10),
            'search' => $request->query->get('search')
        ];

        $result = $this->clientRepository->findBySearch($params);
        $data = $this->serializer->serialize($result, 'json', ['groups' => 'client:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_clients_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($client, 'json', ['groups' => 'client:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_clients_create', methods: ['POST'])]
    #[IsGranted('ROLE_RECEPTIONIST')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setNom($data['nom'] ?? '');
        $client->setTelephone($data['telephone'] ?? null);
        $client->setEmail($data['email'] ?? null);
        $client->setAdresse($data['adresse'] ?? null);
        $client->setIce($data['ice'] ?? null);
        $client->setRc($data['rc'] ?? null);
        $client->setPatente($data['patente'] ?? null);

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($client);
        $this->em->flush();

        $responseData = $this->serializer->serialize($client, 'json', ['groups' => 'client:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_clients_update', methods: ['PUT'])]
    #[IsGranted('ROLE_RECEPTIONIST')]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom']))
            $client->setNom($data['nom']);
        if (isset($data['telephone']))
            $client->setTelephone($data['telephone']);
        if (isset($data['email']))
            $client->setEmail($data['email']);
        if (isset($data['adresse']))
            $client->setAdresse($data['adresse']);
        if (isset($data['ice']))
            $client->setIce($data['ice']);
        if (isset($data['rc']))
            $client->setRc($data['rc']);
        if (isset($data['patente']))
            $client->setPatente($data['patente']);

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($client, 'json', ['groups' => 'client:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_clients_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($client);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
