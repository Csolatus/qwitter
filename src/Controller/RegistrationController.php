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
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, \Symfony\Bundle\SecurityBundle\Security $security): Response
    {
        $user = new User();
        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $pseudo = $request->request->get('pseudo');
        $fullName = $request->request->get('nom_complet');

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
        $user->setFullName($fullName);

        // Simple slug generation
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $pseudo)));
        if (empty($slug)) {
            $slug = 'user-' . uniqid();
        }
        $user->setSlug($slug);

        // This is required for PostgreSQL or strict SQL if google_id is not nullable in DB but nullable in Entity (based on current entity mapping it is NOT nullable in DB but default is null? No, looking at User.php line 49: `private ?string $google_id = null;` and `#[ORM\Column(length: 255)]` means it is NOT nullable in DB effectively unless we set a default or make it nullable. 
        // Wait, looking at User.php line 49: `#[ORM\Column(length: 255)]` -> NOT NULL.
        // But the property is nullable type `?string`. This will crash if we don't set it.
        // Let's check User.php line 49 again.
        // `private ?string $google_id = null;`
        // `#[ORM\Column(length: 255)]`
        // This suggests it IS required. But for a normal registration we don't have a google_id.
        // I should probably make google_id nullable in User.php as well, or set a placeholder.
        // For now, let's assume I need to fix User.php too if I notice it's strict.
        // Actually, looking at the previous User.php content, Google ID was added. I should probably make it nullable if it's not already. 
        // Let's check the User.php content again. 
        // Line 48: `#[ORM\Column(length: 255)]`
        // Line 49: `private ?string $google_id = null;`
        // Yes, it is set to NOT NULL in Doctrine. This will fail normal registration.
        // I will fix User.php google_id to be nullable first/concurrently.

        $user->setGoogleId(''); // Set empty string or handle it. Ideally make it nullable.
        // Actually, better to make it nullable. I will fix User.php in next step or now. 
        // Let's stick to updating controller here, but I'll add a dummy value to prevent crash if I don't fix entity immediately, 
        // or I'll trust I will fix the entity. Let's fix the entity in a separate call to be safe.
        // For now let's set it to empty string if it allows, or just ignore and fix the entity.
        // I'll set it to a placeholder 'manual_registration' or similar if necessary, but making it nullable is best.

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
