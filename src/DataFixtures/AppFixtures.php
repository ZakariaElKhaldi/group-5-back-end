<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $hasher;

    public function __construct(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new \App\Entity\User();
        $admin->setEmail('admin@local.host');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Admin');
        $admin->setPrenom('System');
        $password = $this->hasher->hashPassword($admin, 'password');
        $admin->setPassword($password);
        $manager->persist($admin);

        // Technicien
        $userTech = new \App\Entity\User();
        $userTech->setEmail('tech@local.host');
        $userTech->setRoles(['ROLE_TECHNICIEN']);
        $userTech->setNom('Tech');
        $userTech->setPrenom('One');
        $passwordTech = $this->hasher->hashPassword($userTech, 'password');
        $userTech->setPassword($passwordTech);
        $manager->persist($userTech);

        $tech = new \App\Entity\Technicien();
        $tech->setUser($userTech);
        $tech->setSpecialite('Electrique');
        $tech->setTauxHoraire(50.0);
        $tech->setStatut('Disponible');
        $manager->persist($tech);

        // Receptionist
        $receptionist = new \App\Entity\User();
        $receptionist->setEmail('reception@local.host');
        $receptionist->setRoles(['ROLE_RECEPTIONIST']);
        $receptionist->setNom('Réception');
        $receptionist->setPrenom('Bureau');
        $passwordReception = $this->hasher->hashPassword($receptionist, 'password');
        $receptionist->setPassword($passwordReception);
        $manager->persist($receptionist);

        $manager->flush();
    }
}
