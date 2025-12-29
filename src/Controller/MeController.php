<?php

namespace App\Controller;

use App\Repository\TechnicienRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api')]
class MeController extends AbstractController
{
    public function __construct(
        private TechnicienRepository $technicienRepository
    ) {
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Find technicien profile if exists
        $technicien = $this->technicienRepository->findOneBy(['user' => $user]);

        $response = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'roles' => $user->getRoles(),
            'technicien' => null,
        ];

        if ($technicien) {
            $response['technicien'] = [
                'id' => $technicien->getId(),
                'specialite' => $technicien->getSpecialite(),
                'tauxHoraire' => $technicien->getTauxHoraire(),
                'statut' => $technicien->getStatut(),
            ];
        }

        return $this->json($response);
    }

    #[Route('/me/status', name: 'api_me_status', methods: ['PATCH'])]
    public function updateStatus(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $technicien = $this->technicienRepository->findOneBy(['user' => $user]);
        
        if (!$technicien) {
            return $this->json(['error' => 'Not a technician'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $newStatus = $data['statut'] ?? null;

        $validStatuses = ['Disponible', 'En intervention', 'Absent'];
        if (!$newStatus || !in_array($newStatus, $validStatuses)) {
            return $this->json([
                'error' => 'Invalid status',
                'valid' => $validStatuses
            ], Response::HTTP_BAD_REQUEST);
        }

        $technicien->setStatut($newStatus);
        $this->technicienRepository->getEntityManager()->flush();

        return $this->json([
            'statut' => $technicien->getStatut(),
            'message' => 'Status updated successfully'
        ]);
    }
}
