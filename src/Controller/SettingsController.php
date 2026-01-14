<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
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

        // Instantiation du formulaire (inchangé)
        $formulaire = $this->createForm(ProfileType::class, $utilisateur);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // Traitement du formulaire...
        }

        // Récupération de l'onglet actif (par défaut 'account')
        $onglet = $requete->query->get('tab', 'account');

        // Rendu de la vue avec le formulaire et l'onglet actif
        return $this->render('settings/index.html.twig', [
            'formulaire' => $formulaire->createView(),
            'onglet_actif' => $onglet,
        ]);
    }
}
