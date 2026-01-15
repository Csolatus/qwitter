<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_message_index')]
    public function index(MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        
        // On récupère les messages reçus par l'utilisateur connecté
        // Le champ dans l'entité est 'receiver'
        // Le tri se fait sur 'created_at' (DESC pour avoir les plus récents en haut)
        $messages = $messageRepository->findBy(
            ['receiver' => $user], 
            ['created_at' => 'DESC']
        );

        return $this->render('messages/index.html.twig', [
            'messages' => $messages,
        ]);
    }
}
