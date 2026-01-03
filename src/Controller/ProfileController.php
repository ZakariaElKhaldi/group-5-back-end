<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/me', name: 'api_profile_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => ['user:read', 'technicien:read']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/change-password', name: 'api_profile_change_password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $currentPassword = $data['currentPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        if (!$currentPassword || !$newPassword) {
            return $this->json(['error' => 'Missing password information'], Response::HTTP_BAD_REQUEST);
        }

        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return $this->json(['error' => 'Mot de passe actuel incorrect'], Response::HTTP_BAD_REQUEST);
        }

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $this->em->flush();

        return $this->json(['message' => 'Mot de passe modifié avec succès']);
    }
}
