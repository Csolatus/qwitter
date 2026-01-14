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

class SettingsController extends AbstractController
{
    #[Route('/parametres', name: 'app_parametres')]
    public function index(Request $requete): Response
    {
        // TODO: Récupérer l'utilisateur connecté via $this->getUser()
        // Pour l'instant, on crée un utilisateur vide pour l'affichage
        $utilisateur = new User();
        $utilisateur->setPseudo('UtilisateurTest');
        $utilisateur->setEmail('test@qwitter.com');

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
            // Traitement du formulaire...
            // $entityManager->persist($utilisateur);
            // $entityManager->flush();
        }

        // Rendu de la vue avec le formulaire et l'onglet actif
        return $this->render('settings/index.html.twig', [
            'formulaire' => $formulaire->createView(),
            'onglet_actif' => $onglet,
        ]);
    }
}
