<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    /**
     * Cette route gère À LA FOIS la liste vide et une conversation spécifique
     * Si {id} est présent, on affiche la conversation avec cet utilisateur.
     */
    #[Route('/messages', name: 'app_message_index')]
    #[Route('/messages/{id}', name: 'app_message_show')]
    public function index(
        MessageRepository $messageRepository,
        UserRepository $userRepository,
        Request $request, 
        EntityManagerInterface $em,
        ?User $otherUser = null // L'utilisateur avec qui on parle (optionnel)
    ): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        // 1. Gestion de l'envoi de message
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($user);
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setIsRead(false);

            // Si on est dans une conversation spécifique, on force le destinataire
            if ($otherUser) {
                $message->setReceiver($otherUser);
            }

            $em->persist($message);
            $em->flush();

            // Redirection vers la conversation pour voir le message envoyé
            return $this->redirectToRoute('app_message_show', ['id' => $message->getReceiver()->getId()]);
        }

        // 2. Récupération des données pour la vue
        
        // Liste de TOUS les utilisateurs (pour la sidebar de gauche)
        // Dans le futur, tu pourras filtrer pour ne montrer que les "amis" ou "ceux avec qui j'ai parlé"
        $users = $userRepository->findAll();

        // Si un utilisateur est sélectionné, on charge la conversation
        $conversation = [];
        if ($otherUser) {
            $conversation = $messageRepository->findConversation($user, $otherUser);
            
            // Marquer les messages comme "lus" (optionnel mais cool)
            foreach ($conversation as $msg) {
                if ($msg->getReceiver() === $user && !$msg->isRead()) {
                    $msg->setIsRead(true);
                    $em->persist($msg);
                }
            }
            $em->flush();
        }

        return $this->render('messages/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users,           // Pour la liste de gauche
            'conversation' => $conversation, // Les messages de la conversation active
            'otherUser' => $otherUser,   // L'utilisateur avec qui on parle
        ]);
    }

    #[Route('/messages/delete/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(
        Message $message, 
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        $user = $this->getUser();

        // Sécurité
        if ($message->getReceiver() !== $user && $message->getSender() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce message.');
        }

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
        }

        // Redirection intelligente : on reste sur la conversation en cours
        // Si j'ai envoyé le message, je veux retourner voir le destinataire
        // Si j'ai reçu le message, je veux retourner voir l'expéditeur
        $redirectUserId = ($message->getSender() === $user) 
            ? $message->getReceiver()->getId() 
            : $message->getSender()->getId();

        return $this->redirectToRoute('app_message_show', ['id' => $redirectUserId]);
    }
}
