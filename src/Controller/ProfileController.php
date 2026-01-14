<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/@{pseudo}', name: 'app_profile_show', priority: 1)]
    public function show(string $pseudo, UserRepository $repo): Response
    {
        $user = $repo->findOneBy(['pseudo' => $pseudo]);
        if (!$user) throw $this->createNotFoundException("Utilisateur introuvable");
        
        return $this->render('profile/show.html.twig', ['user' => $user]);
    }
}
