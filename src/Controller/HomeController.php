<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        \Symfony\Bundle\SecurityBundle\Security $security,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \App\Repository\PostRepository $postRepository,
        \Symfony\Component\HttpFoundation\Request $request
    ): Response {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Handle new post creation
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            if (!empty($content)) {
                $post = new \App\Entity\Post();
                $post->setContent($content);
                $post->setAuthor($user);
                $post->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($post);
                $entityManager->flush();

                return $this->redirectToRoute('home');
            }
        }

        // Fetch posts
        $posts = $postRepository->findBy([], ['created_at' => 'DESC']);

        return $this->render('home/feed.html.twig', [
            'posts' => $posts
        ]);
    }
}
