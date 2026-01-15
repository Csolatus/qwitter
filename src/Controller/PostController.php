<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
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
    public function like(Post $post, LikeRepository $likeRepo, EntityManagerInterface $entityManager, Request $request): Response
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
        }

        $entityManager->flush();

        // If AJAX request, could return JSON. For now, redirect back.
        // We look at the referer to redirect to the same page
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
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
