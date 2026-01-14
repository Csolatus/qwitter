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
        // Création d'un User "Admin"
        $admin = new User();
        $admin->setEmail('admin@qwitter.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPseudo('AdminQwitter');
        $admin->setSlug('admin-qwitter');
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // Création d'un User "Basique"
        $user = new User();
        $user->setEmail('user@qwitter.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPseudo('UserTest');
        $user->setSlug('user-test');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $manager->flush();
    }
}
