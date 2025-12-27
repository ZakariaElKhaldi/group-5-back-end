<?php

namespace App\Controller;

use App\Entity\Technicien;
use App\Entity\User;
use App\Repository\TechnicienRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/techniciens')]
class TechnicienController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TechnicienRepository $technicienRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('', name: 'api_techniciens_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $statut = $request->query->get('statut', '');

        $qb = $this->technicienRepository->createQueryBuilder('t')
            ->leftJoin('t.user', 'u')
            ->addSelect('u');

        if ($statut) {
            $qb->andWhere('t.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $techniciens = $qb->getQuery()->getResult();
        $data = $this->serializer->serialize($techniciens, 'json', ['groups' => 'technicien:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_techniciens_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $technicien = $this->technicienRepository->find($id);
        if (!$technicien) {
            return $this->json(['error' => 'Technicien not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $this->serializer->serialize($technicien, 'json', ['groups' => 'technicien:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'api_techniciens_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create user for technician
        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setNom($data['nom'] ?? '');
        $user->setPrenom($data['prenom'] ?? '');
        $user->setRoles(['ROLE_TECHNICIEN']);

        $password = $data['password'] ?? 'password123';
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Create technician
        $technicien = new Technicien();
        $technicien->setUser($user);
        $technicien->setSpecialite($data['specialite'] ?? '');
        $technicien->setTauxHoraire((float) ($data['tauxHoraire'] ?? 50.0));
        $technicien->setStatut($data['statut'] ?? 'Disponible');

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($technicien);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->persist($technicien);
        $this->em->flush();

        $responseData = $this->serializer->serialize($technicien, 'json', ['groups' => 'technicien:read']);
        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_techniciens_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $technicien = $this->technicienRepository->find($id);
        if (!$technicien) {
            return $this->json(['error' => 'Technicien not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['specialite']))
            $technicien->setSpecialite($data['specialite']);
        if (isset($data['tauxHoraire']))
            $technicien->setTauxHoraire((float) $data['tauxHoraire']);
        if (isset($data['statut']))
            $technicien->setStatut($data['statut']);

        // Update user info if provided
        $user = $technicien->getUser();
        if (isset($data['nom']))
            $user->setNom($data['nom']);
        if (isset($data['prenom']))
            $user->setPrenom($data['prenom']);
        if (isset($data['email']))
            $user->setEmail($data['email']);

        $errors = $this->validator->validate($technicien);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        $responseData = $this->serializer->serialize($technicien, 'json', ['groups' => 'technicien:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/interventions', name: 'api_techniciens_interventions', methods: ['GET'])]
    public function getInterventions(int $id): JsonResponse
    {
        $technicien = $this->technicienRepository->find($id);
        if (!$technicien) {
            return $this->json(['error' => 'Technicien not found'], Response::HTTP_NOT_FOUND);
        }

        $interventions = $technicien->getInterventions();
        $data = $this->serializer->serialize($interventions, 'json', ['groups' => 'intervention:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_techniciens_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $technicien = $this->technicienRepository->find($id);
        if (!$technicien) {
            return $this->json(['error' => 'Technicien not found'], Response::HTTP_NOT_FOUND);
        }

        // Also remove the linked user
        $user = $technicien->getUser();

        $this->em->remove($technicien);
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
