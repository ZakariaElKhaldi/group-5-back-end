<?php

namespace App\Controller;

use App\Entity\NotificationRead;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'api_notifications_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $limit = $request->query->getInt('limit', 20);

        $notifications = $this->notificationRepository->findUnreadForUser($user, $limit);
        $data = $this->serializer->serialize($notifications, 'json', ['groups' => 'notification:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/read', name: 'api_notifications_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();

        // Check if already marked as read by this user
        $existingRead = $this->em->getRepository(NotificationRead::class)
            ->findOneBy(['user' => $user, 'notification' => $notification]);

        if (!$existingRead) {
            $notificationRead = new NotificationRead();
            $notificationRead->setUser($user);
            $notificationRead->setNotification($notification);

            $this->em->persist($notificationRead);
            $this->em->flush();
        }

        return $this->json(['success' => true]);
    }
}
