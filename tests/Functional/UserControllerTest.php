<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testGetUsers()
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testEmptyArrayIfNoUsers()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);

        $entityManager->beginTransaction();

        try {
            $userRepository = $entityManager->getRepository(User::class);
            $existingUsers = $userRepository->findAll();

            foreach ($existingUsers as $user) {
                $entityManager->remove($user);
            }
            $entityManager->flush();

            $client->request('GET', '/api/users');

            $this->assertResponseIsSuccessful();
            $this->assertJson($client->getResponse()->getContent());
            $this->assertJsonStringEqualsJsonString('[]', $client->getResponse()->getContent());

            $entityManager->rollback();
        } catch (\Exception $e) {
            $entityManager->rollback();
            throw $e;
        }
    }
}
