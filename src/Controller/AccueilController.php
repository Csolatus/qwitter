<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(PostRepository $postRepo, UserRepository $userRepo): Response
    {
        // 1. Récupère tous les posts triés par date décroissante (les plus récents en premier)
        $posts = $postRepo->findBy([], ['created_at' => 'DESC']);

        // 2. Récupère 3 utilisateurs au hasard ou les derniers inscrits pour les suggestions
        // (Tu pourras améliorer cette logique plus tard pour exclure l'utilisateur courant)
        $suggestions = $userRepo->findBy([], ['created_at' => 'DESC'], 3);

        return $this->render('accueil/index.html.twig', [
            'posts' => $posts,
            'suggestions' => $suggestions,
        ]);
    }
}
