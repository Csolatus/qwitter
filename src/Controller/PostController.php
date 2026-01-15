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

        // If AJAX request, could return JSON. For now, redirect back.
        // We look at the referer to redirect to the same page
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }

    #[Route('/{id}/repost', name: 'app_post_repost', methods: ['POST', 'GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function repost(Post $post, EntityManagerInterface $entityManager, Request $request, NotificationService $notificationService): Response
    {
        $user = $this->getUser();

        // Check if already reposted? Logic might get complex, for now simple implementation
        $repost = new Post();
        $repost->setAuthor($user);
        $repost->setContent(''); // Repost content usually empty or copied? Let's say empty and we rely on originalPost
        $repost->setOriginalPost($post);
        // CreatedAt handled by constructor or lifecycle? Post doesn't have constructor for createdAt yet?
        // Let's check Post entity... we verified 'createdAt' property exists but not if it's auto-set.
        // User's previous error on Like suggests we need to set it manually if not in constructor.
        // Let's safe-set it here.
        $repost->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($repost);
        $entityManager->flush();

        $notificationService->notify($post->getAuthor(), 'repost', "a republié votre post", $user, $post->getId());

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        // Fetch comments? Or rely on $post->getComments() (Lazy loading)
        // Lazy loading is fine for now.
        // We might want to sort comments? They are Collection, can do logic in template or separate query.
        // Let's rely on default ordering or sort in Twig/Entity if needed. 
        // We can pass them explicitly if we want to sort DESC.

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Post $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce post.');
        }

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            // Remove image if exists
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
