<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/fournisseurs')]
class FournisseurController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FournisseurRepository $fournisseurRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_fournisseurs_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $fournisseurs = $this->fournisseurRepository->findAll();
        $data = $this->serializer->serialize($fournisseurs, 'json', ['groups' => 'fournisseur:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_fournisseurs_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $fournisseur = $this->fournisseurRepository->find($id);
        if (!$fournisseur) {
            return $this->json(['error' => 'Fournisseur not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($fournisseur, 'json', ['groups' => 'fournisseur:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_fournisseurs_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $fournisseur = new Fournisseur();
        $fournisseur->setNom($data['nom'] ?? '');
        $fournisseur->setEmail($data['email'] ?? null);
        $fournisseur->setTelephone($data['telephone'] ?? null);
        $fournisseur->setAdresse($data['adresse'] ?? null);
        $fournisseur->setDelaiLivraison($data['delaiLivraison'] ?? null);

        $errors = $this->validator->validate($fournisseur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($fournisseur);
        $this->em->flush();

        $responseData = $this->serializer->serialize($fournisseur, 'json', ['groups' => 'fournisseur:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_fournisseurs_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $fournisseur = $this->fournisseurRepository->find($id);
        if (!$fournisseur) {
            return $this->json(['error' => 'Fournisseur not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom']))
            $fournisseur->setNom($data['nom']);
        if (isset($data['email']))
            $fournisseur->setEmail($data['email']);
        if (isset($data['telephone']))
            $fournisseur->setTelephone($data['telephone']);
        if (isset($data['adresse']))
            $fournisseur->setAdresse($data['adresse']);
        if (isset($data['delaiLivraison']))
            $fournisseur->setDelaiLivraison($data['delaiLivraison']);

        $errors = $this->validator->validate($fournisseur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($fournisseur, 'json', ['groups' => 'fournisseur:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_fournisseurs_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $fournisseur = $this->fournisseurRepository->find($id);
        if (!$fournisseur) {
            return $this->json(['error' => 'Fournisseur not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if fournisseur has pieces linked
        if (!$fournisseur->getPieces()->isEmpty()) {
            return $this->json(
                ['error' => 'Cannot delete supplier with linked parts'],
                Response::HTTP_CONFLICT
            );
        }

        $this->em->remove($fournisseur);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
