<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    //    /**
    //     * @return Message[] Returns an array of Message objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Message
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Trouve la conversation complète entre deux utilisateurs (messages envoyés ET reçus)
     */
    public function findConversation(\App\Entity\User $user1, \App\Entity\User $user2)
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2)')
            ->orWhere('(m.sender = :user2 AND m.receiver = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.created_at', 'ASC') // ASC pour lire du haut vers le bas (plus ancien au plus récent)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a list of contacts with metadata (last message date, unread count)
     */
    public function findRecentContacts(\App\Entity\User $me): array
    {
        $messages = $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->leftJoin('m.receiver', 'r')
            ->addSelect('s', 'r')
            ->where('m.sender = :me OR m.receiver = :me')
            ->setParameter('me', $me)
            ->orderBy('m.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        $contacts = [];

        foreach ($messages as $message) {
            // Determine the "other" user
            if ($message->getSender() === $me) {
                $otherUser = $message->getReceiver();
                // I sent it, so it's not unread for me
                $isUnread = false;
            } else {
                $otherUser = $message->getSender();
                // I received it, check if unread
                $isUnread = !$message->isRead();
            }

            if (!$otherUser)
                continue; // Should not happen but safety first

            $id = $otherUser->getId();

            if (!isset($contacts[$id])) {
                $contacts[$id] = [
                    'user' => $otherUser,
                    'lastMessageAt' => $message->getCreatedAt(),
                    'unreadCount' => 0
                ];
            }

            if ($isUnread) {
                $contacts[$id]['unreadCount']++;
            }
        }

        // Sort is already roughly implicitly by creation (since we iterated desc), 
        // but because we group by ID, the first time we see a user is their most recent message.
        // So simply taking array_values preserves the order of "first appearance" which is "most recent".
        // No explicit sort needed if we trust the loop order!
        // But let's be safe and return indexed array.

        return array_values($contacts);
    }
}

