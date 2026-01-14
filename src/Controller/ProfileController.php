<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profil/{slug}', name: 'app_profile', defaults: ['slug' => null])]
    public function index(?string $slug, EntityManagerInterface $entityManager): Response
    {
        // Si aucun slug n'est fourni, on redirige vers le profil de l'utilisateur connecté
        if (!$slug) {
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('home');
            }
            /** @var User $user */
            // Idéalement on utiliserait le slug de l'utilisateur, 
            // mais ici on peut aussi juste afficher le profil courant si pas de slug.
            // Pour être propre, redirigeons vers l'url canonique avec slug.
            if ($user->getSlug()) {
                return $this->redirectToRoute('app_profile', ['slug' => $user->getSlug()]);
            }
        } else {
            // Recherche de l'utilisateur par slug
            $user = $entityManager->getRepository(User::class)->findOneBy(['slug' => $slug]);

            if (!$user) {
                throw $this->createNotFoundException('Utilisateur non trouvé');
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user, // L'utilisateur dont on regarde le profil
            'is_me' => $this->getUser() === $user, // Est-ce mon propre profil ?
        ]);
    }
}
