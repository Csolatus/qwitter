<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(
        PostRepository $postRepo,
        UserRepository $userRepo,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }

            $post->setAuthor($user);
            $post->setCreatedAt(new \DateTimeImmutable());

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('posts_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $post->setImageFilename($newFilename);
                $post->setMediaType('image'); // Simplified for now
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }

        // Gestion des onglets "Pour vous" / "Abonnements"
        $feedType = $request->query->get('feed', 'foryou');
        $user = $this->getUser();

        if ($feedType === 'following' && $user) {
            // Récupérer les ID des utilisateurs suivis
            $followedIds = [];
            foreach ($user->getFollowing() as $followedUser) {
                $followedIds[] = $followedUser->getId();
            }
            // Ajouter l'ID de l'utilisateur courant pour voir aussi ses propres posts
            $followedIds[] = $user->getId();

            $posts = $postRepo->findByAuthors($followedIds);
        } else {
            // Par défaut ("foryou") ou si non connecté : tous les posts
            $posts = $postRepo->findBy([], ['created_at' => 'DESC']);
        }

        // 2. Récupère 3 utilisateurs au hasard ou les derniers inscrits pour les suggestions
        $suggestions = $userRepo->findBy([], ['created_at' => 'DESC'], 3);

        return $this->render('accueil/index.html.twig', [
            'posts' => $posts,
            'suggestions' => $suggestions,
            'form' => $form->createView(),
            'current_feed' => $feedType,
        ]);
    }
}
