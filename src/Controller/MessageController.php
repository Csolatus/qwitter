<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_message_index')]
    public function index(
        MessageRepository $messageRepository, 
        Request $request, 
        EntityManagerInterface $em
    ): Response
    {
        $user = $this->getUser();
        
        // 1. Création du formulaire d'envoi
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($user);
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setIsRead(false);

            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message envoyé avec succès !');
            return $this->redirectToRoute('app_message_index');
        }

        // 2. Liste des messages reçus
        $messages = $messageRepository->findBy(
            ['receiver' => $user], 
            ['created_at' => 'DESC']
        );

        return $this->render('messages/index.html.twig', [
            'messages' => $messages,
            'form' => $form->createView()
        ]);
    }

    #[Route('/messages/delete/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(
        Message $message, 
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        // Sécurité : Vérifier que l'utilisateur est bien le destinataire ou l'expéditeur
        if ($message->getReceiver() !== $this->getUser() && $message->getSender() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce message.');
        }

        // Vérification CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }

        return $this->redirectToRoute('app_message_index');
    }
}
