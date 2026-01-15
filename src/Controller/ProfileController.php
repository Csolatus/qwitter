<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil/{slug}', name: 'app_profile', defaults: ['slug' => null])]
    public function index(?string $slug, EntityManagerInterface $entityManager, Request $request): Response
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

        // Gestion des onglets
        $tab = $request->query->get('tab', 'posts');
        $data = [];

        // Vérification de la confidentialité du compte
        $currentUser = $this->getUser();
        $isPrivateAccessDenied = false;

        // Si le compte est privé ET (ce n'est pas moi ET je ne suis pas abonné)
        if ($user->isPrivate() && $user !== $currentUser && (!$currentUser || !$user->getFollowers()->contains($currentUser))) {
            $isPrivateAccessDenied = true;
            $data = []; // On vide les données pour ne rien afficher
        } else {
            // Logique de récupération normale
            switch ($tab) {
                case 'likes':
                    $likes = $entityManager->getRepository(\App\Entity\Like::class)->findBy(
                        ['user' => $user],
                        ['created_at' => 'DESC']
                    );
                    $data = array_map(fn($like) => $like->getPost(), $likes);
                    break;

                case 'replies':
                    $data = $entityManager->getRepository(\App\Entity\Comment::class)->findBy(
                        ['author' => $user],
                        ['created_at' => 'DESC']
                    );
                    break;

                case 'posts':
                default:
                    $data = $entityManager->getRepository(\App\Entity\Post::class)->findBy(
                        ['author' => $user],
                        ['created_at' => 'DESC']
                    );
                    break;
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user, // L'utilisateur dont on regarde le profil
            'data' => $data,
            'current_tab' => $tab,
            'is_me' => $currentUser === $user, // Est-ce mon propre profil ?
            'is_private_access_denied' => $isPrivateAccessDenied,
        ]);
    }
}
