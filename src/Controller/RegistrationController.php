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
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, \Symfony\Bundle\SecurityBundle\Security $security): Response
    {
        if ($request->isMethod('GET')) {
            return $this->redirectToRoute('app_login', ['register' => 'true']);
        }
        $user = new User();
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $pseudo = $request->request->get('pseudo');
        $fullName = $request->request->get('nom_complet');

        if (!$email || !$plainPassword || !$pseudo) {
            $this->addFlash('error', 'All fields are required.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user exists
        $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $this->addFlash('error', 'Email already in use.');
            return $this->redirectToRoute('app_login');
        }

        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setFullName($fullName);

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

        // Auto login
        return $security->login($user, 'form_login', 'main');
    }
}
