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

class SettingsController extends AbstractController
{
    #[Route('/parametres', name: 'app_parametres')]
    public function index(Request $requete, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $utilisateur */
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            return $this->redirectToRoute('home');
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

        // Rendu de la vue avec le formulaire et l'onglet actif
        return $this->render('settings/index.html.twig', [
            'formulaire' => $formulaire->createView(),
            'onglet_actif' => $onglet,
        ]);
    }
}
