<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/{id}/add', name: 'app_comment_add', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function add(Post $post, Request $request, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        $content = $request->request->get('content');

        if ($content) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            // Notification
            $notificationService->notify(
                $post->getAuthor(),
                'comment',
                "a répondu à votre post",
                $this->getUser(),
                $post->getId(),
                $comment->getContent() // Pass content preview
            );
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }
}
