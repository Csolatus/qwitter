<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $notifications = $entityManager->getRepository(Notification::class)->findBy(
            ['user' => $user],
            ['created_at' => 'DESC']
        );

        // Mark as read
        $unreadNotificationIds = [];
        foreach ($notifications as $notification) {
            if (!$notification->isRead()) {
                $unreadNotificationIds[] = $notification->getId();
                $notification->setIsRead(true);
            }
        }
        $entityManager->flush();

        // Optional: Pre-fetch related posts if needed for performance or previews
        // For now, we rely on the message.

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadNotificationIds' => $unreadNotificationIds,
        ]);
    }
}
