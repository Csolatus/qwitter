<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
class PostController extends AbstractController
{
    /**
     * Gère l'action de "Like" sur un post.
     * Si le like existe déjà, il est supprimé (unlike). Sinon, il est créé.
     */
    #[Route('/{id}/like', name: 'app_post_like', methods: ['POST', 'GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(Post $post, LikeRepository $likeRepo, EntityManagerInterface $entityManager, Request $request, NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        $existingLike = $likeRepo->findOneBy(['post' => $post, 'user' => $user]);

        if ($existingLike) {
            $entityManager->remove($existingLike);
        } else {
            $like = new Like();
            $like->setPost($post);
            $like->setUser($user);
            $entityManager->persist($like);

            // Notification
            $notificationService->notify($post->getAuthor(), 'like', "a aimé votre post", $user, $post->getId());
        }

        $entityManager->flush();

        // Récupère l'URL précédente pour rediriger l'utilisateur au même endroit
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }

    /**
     * Permet à un utilisateur de republier (repost) un message existant.
     */
    #[Route('/{id}/repost', name: 'app_post_repost', methods: ['POST', 'GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function repost(Post $post, EntityManagerInterface $entityManager, Request $request, NotificationService $notificationService): Response
    {
        $user = $this->getUser();

        // Vérifie si déjà reposté? Logique simplifiée pour l'instant
        $repost = new Post();
        $repost->setAuthor($user);
        $repost->setContent(''); // contenu vide, on se base sur l'originalPost
        $repost->setOriginalPost($post);

        // On définit la date de création manuellement
        $repost->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($repost);
        $entityManager->flush();

        $notificationService->notify($post->getAuthor(), 'repost', "a republié votre post", $user, $post->getId());

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }

    /**
     * Affiche le détail d'un post spécifique.
     */
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        // Les commentaires sont chargés via le lazy loading de Doctrine dans le template si besoin

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Supprime un post.
     * Vérifie si l'utilisateur est l'auteur ou un administrateur.
     * Supprime également l'image associée si elle existe.
     */
    #[Route('/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Post $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce post.');
        }

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            // Suppression de l'image si elle existe
            if ($post->getImageFilename()) {
                $imagePath = $this->getParameter('posts_directory') . '/' . $post->getImageFilename();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accueil');
    }
}
