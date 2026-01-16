<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\PrivacyType;
use App\Form\SecurityType;
use App\Form\BillingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SettingsController extends AbstractController
{
    #[Route('/parametres', name: 'app_parametres')]
    public function index(
        Request $requete,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger,
        #[Autowire('%avatars_directory%')] string $avatarsDirectory
    ): Response {
        /** @var User $utilisateur */
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération de l'onglet actif (par défaut 'account')
        $onglet = $requete->query->get('tab', 'account');

        // Sélection du formulaire en fonction de l'onglet
        $formClass = ProfileType::class;
        if ($onglet === 'privacy') {
            $formClass = PrivacyType::class;
        } elseif ($onglet === 'security') {
            $formClass = SecurityType::class;
        } elseif ($onglet === 'billing') {
            $formClass = BillingType::class;
        }

        // Instantiation du formulaire
        $formulaire = $this->createForm($formClass, $utilisateur);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {

            // Gestion de l'upload d'avatar (onglet account)
            if ($onglet === 'account') {
                /** @var UploadedFile $avatarFile */
                $avatarFile = $formulaire->get('avatar')->getData();

                if ($avatarFile) {
                    $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                    try {
                        $avatarFile->move($avatarsDirectory, $newFilename);
                        $utilisateur->setAvatar($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de votre image.');
                    }
                }
            }

            // Gestion spécifique pour le mot de passe si on est dans l'onglet sécurité
            if ($onglet === 'security') {
                $newPassword = $formulaire->get('newPassword')->getData();
                if ($newPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
                    $utilisateur->setPassword($hashedPassword);
                }
            }

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Vos paramètres ont été mis à jour avec succès.');

            return $this->redirectToRoute('app_parametres', ['tab' => $onglet]);
        }

        // Formulaire de suppression de compte (uniquement pour l'onglet advanced)
        $deleteForm = null;
        if ($onglet === 'advanced') {
            $deleteForm = $this->createForm(\App\Form\DeleteAccountType::class, null, [
                'action' => $this->generateUrl('app_account_delete'),
                'method' => 'POST',
            ]);
        }

        // Rendu de la vue avec le formulaire et l'onglet actif
        return $this->render('settings/index.html.twig', [
            'formulaire' => $formulaire->createView(),
            'onglet_actif' => $onglet,
            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
        ]);
    }

    #[Route('/parametres/supprimer', name: 'app_account_delete', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $entityManager,
        \Symfony\Bundle\SecurityBundle\Security $security,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(\App\Form\DeleteAccountType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Re-fetch user to verify existence and ensure it is attached to the current EntityManager
                $userToDelete = $entityManager->getRepository(\App\Entity\User::class)->find($user->getId());

                if (!$userToDelete) {
                    throw new \Exception('Utilisateur non trouvé en base de données.');
                }

                // Remove user
                $entityManager->remove($userToDelete);
                $entityManager->flush();

                // Logout manually AFTER flush
                $security->logout(false);

                $this->addFlash('success', 'Votre compte a été supprimé definitivement.');
                return $this->redirectToRoute('app_accueil');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du compte : ' . $e->getMessage());
                return $this->redirectToRoute('app_parametres', ['tab' => 'advanced']);
            }
        } else {
            $errors = [];
            foreach ($form->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }

            if (!empty($errors)) {
                $this->addFlash('error', 'Validation échouée : ' . implode(', ', $errors));
            } else {
                $this->addFlash('error', 'Impossible de supprimer le compte. Vérifiez votre mot de passe et la case à cocher.');
            }
        }

        return $this->redirectToRoute('app_parametres', ['tab' => 'advanced']);
    }
}
