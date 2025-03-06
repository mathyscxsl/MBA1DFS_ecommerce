<?php

namespace App\Tests;


use Symfony\Component\Panther\PantherTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends PantherTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Nettoyer la base de données avant chaque test
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        // Créer un utilisateur de test
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_USER']);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'password123'
        );
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testSuccessLogin(): void
    {
        // Accéder à la page de connexion
        $crawler = $this->client->request('GET', '/login');

        // Vérifier le titre de la page
        $this->assertSelectorTextContains('h1', 'Please sign in');

        // Remplir le formulaire
        $form = $crawler->filter('form')->form([
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        // Soumettre le formulaire
        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/');

        // Suivre la redirection
        $this->client->followRedirect();

        // Vérifier que nous sommes sur la page d'accueil
        $this->assertSelectorTextContains('h1', 'Boutique Symfony');

        // Vérifier que l'utilisateur existe dans la base de données
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        $this->assertNotNull($user, 'L\'utilisateur devrait exister dans la base de données');
        $this->assertEquals('admin@example.com', $user->getEmail());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
