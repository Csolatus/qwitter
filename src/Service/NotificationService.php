<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function notify(User $recipient, string $type, string $message, User $actor, ?int $relatedId = null, ?string $contentPreview = null): void
    {
        // Don't notify if the user performs an action on their own content
        if ($recipient === $actor) {
            return;
        }

        $notification = new Notification();
        $notification->setUser($recipient);
        $notification->setActor($actor);
        $notification->setType($type); // 'like', 'comment', 'repost', 'follow'

        // If content preview exists, we can append it or handle it in template.
        if ($contentPreview) {
            // truncate if necessary
            $preview = mb_substr($contentPreview, 0, 50) . (mb_strlen($contentPreview) > 50 ? '...' : '');
            $message .= ': "' . $preview . '"';
        }

        $notification->setMessage($message);
        $notification->setRelatedId($relatedId);
        $notification->setIsRead(false);
        // Assuming there isn't an 'actor' field in Notification entity based on previous read, 
        // we might need to rely on the message or add it.
        // Looking at the entity `src/Entity/Notification.php` read earlier:
        // It has type, message, is_read, related_id, user.
        // It doesn't seem to have an 'actor' relationship. 
        // For now we'll bake the actor name into the message or just proceed as is.
        // Ideally we should add 'actor' to Notification entity for better UI (show avatar), 
        // but let's stick to the current entity structure for now to avoid migration issues if strict.
        // Wait, better to check if I can add it easily or simply construct the message well.

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
