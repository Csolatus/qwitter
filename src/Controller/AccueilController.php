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
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
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
                        $post->setImageFilename($newFilename);
                        $post->setMediaType('image');
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
                    }
                }

                // Pour autoriser le post sans texte s'il y a une image (vérif manuelle si besoin, mais nullable géré par Doctrine)
                if (empty($post->getContent()) && !$post->getImageFilename()) {
                    $this->addFlash('error', 'Votre post ne peut pas être vide (ajoutez du texte ou une image).');
                } else {
                    $this->addFlash('success', 'Post publié avec succès !');

                    // Gestion du Sondage
                    $pollQuestion = $form->get('pollQuestion')->getData();
                    if (!empty($pollQuestion)) {
                        $poll = new \App\Entity\Poll();
                        $poll->setQuestion($pollQuestion);
                        $poll->setPost($post);
                        $poll->setEndsAt(new \DateTime('+1 day'));

                        // Options
                        for ($i = 1; $i <= 4; $i++) {
                            $optionLabel = $form->get('pollOption' . $i)->getData();
                            if (!empty($optionLabel)) {
                                $option = new \App\Entity\PollOption();
                                $option->setLabel($optionLabel);
                                $poll->addOption($option);
                            }
                        }

                        if ($poll->getOptions()->count() >= 2) {
                            $entityManager->persist($poll);
                        }
                    }

                    $entityManager->persist($post);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_accueil');
                }
            } else {
                // Formulaire invalide : afficher les erreurs globales
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        // Gestion des onglets "Pour vous" / "Abonnements"
        $feedType = $request->query->get('feed', 'foryou');
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, on force le mode "foryou"
        if (!$user) {
            $feedType = 'foryou';
        }

        if ($feedType === 'following' && $user) {
            $posts = $postRepo->findFollowingFeed($user);
        } else {
            // "Pour vous" (Feed par défaut)
            $posts = $postRepo->findForYou($user);
        }

        // 2. Suggestions (Exclure les abonnements et soi-même)
        if ($user) {
            $allUsers = $userRepo->findAll();
            $suggestions = [];

            $following = $user->getFollowing()->toArray();
            $followingIds = array_map(fn($u) => $u->getId(), $following);
            $followingIds[] = $user->getId();

            foreach ($allUsers as $potentialUser) {
                if (!in_array($potentialUser->getId(), $followingIds)) {
                    $suggestions[] = $potentialUser;
                }
            }
            // Shuffle and take 3
            shuffle($suggestions);
            $suggestions = array_slice($suggestions, 0, 3);
        } else {
            // Si pas connecté, suggestions aléatoires simples
            $suggestions = $userRepo->findBy([], ['created_at' => 'DESC'], 3);
        }

        return $this->render('accueil/index.html.twig', [
            'posts' => $posts,
            'suggestions' => $suggestions,
            'form' => $form->createView(),
            'current_feed' => $feedType,
        ]);
    }
}
