<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $pseudo = $request->request->get('pseudo');
        // $nomComplet = $request->request->get('nom_complet');

        if (!$email || !$plainPassword || !$pseudo) {
            $this->addFlash('error', 'All fields are required.');
            return $this->redirectToRoute('home');
        }

        // Check if user exists
        $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $this->addFlash('error', 'Email already in use.');
            return $this->redirectToRoute('home');
        }

        $user->setEmail($email);
        $user->setPseudo($pseudo);
        // Simple slug generation
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $pseudo)));
        if (empty($slug)) {
            $slug = 'user-' . uniqid();
        }
        $user->setSlug($slug);

        $user->setCreatedAt(new \DateTimeImmutable());

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
