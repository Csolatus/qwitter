<?php

namespace App\EventSubscriber;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    private $security;
    private $twig;
    private $entityManager;

    public function __construct(Security $security, Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            // Count unread notifications (excluding messages)
            $notificationCount = $this->entityManager->getRepository(Notification::class)->countUnreadNormalNotifications($user);
            $this->twig->addGlobal('unread_notification_count', $notificationCount);

            // Count unread messages
            $messageCount = $this->entityManager->getRepository(\App\Entity\Message::class)->count([
                'receiver' => $user,
                'is_read' => false
            ]);
            $this->twig->addGlobal('unread_messages_count', $messageCount);
        } else {
            $this->twig->addGlobal('unread_notification_count', 0);
            $this->twig->addGlobal('unread_messages_count', 0);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
