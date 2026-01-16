<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        // 1. Création des utilisateurs spécifiques (Admin & Démo)
        $admin = $this->createUser($manager, 'admin@qwitter.com', 'AdminQwitter', ['ROLE_ADMIN']);
        $demo = $this->createUser($manager, 'demo@qwitter.com', 'DemoUser', ['ROLE_USER']);
        $users[] = $admin;
        $users[] = $demo;

        // 2. Création de 15 utilisateurs fictifs
        for ($i = 1; $i <= 15; $i++) {
            $user = $this->createUser($manager, "user$i@qwitter.com", "User$i", ['ROLE_USER']);
            $users[] = $user;
        }

        // 3. Création de Posts pour chaque utilisateur
        foreach ($users as $user) {
            // Chaque utilisateur publie entre 0 et 5 fois
            $nbPosts = mt_rand(0, 5);
            for ($j = 0; $j < $nbPosts; $j++) {
                $post = new \App\Entity\Post();
                $post->setContent("Ceci est un post généré automatiquement par " . $user->getPseudo() . ". #Qwitter #Demo " . mt_rand(100, 999));
                $post->setAuthor($user);
                $post->setCreatedAt(new \DateTimeImmutable('-' . mt_rand(1, 30) . ' days'));
                $manager->persist($post);
            }
        }

        // 4. Création des abonnements (Aléatoire)
        foreach ($users as $user) {
            // suit 3 à 7 autres utilisateurs
            $targets = $users;
            shuffle($targets);
            $nbFollows = mt_rand(3, 7);

            for ($k = 0; $k < $nbFollows; $k++) {
                $target = $targets[$k];
                if ($target !== $user) { // Ne pas se suivre soi-même
                    $user->addFollowing($target);
                }
            }
        }

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $pseudo, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setSlug(strtolower($pseudo));
        $user->setRoles($roles);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        $manager->persist($user);
        return $user;
    }
}
