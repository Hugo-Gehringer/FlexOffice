<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SpaceControllerTest extends WebTestCase
{
    public function testIndexRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/space/');
        $this->assertResponseRedirects('/login'); // Ou la route de ton login
    }

    public function testIndexDisplaysSpacesForLoggedInUser(): void
    {
        $client = static::createClient();

        // On récupère un utilisateur (modifie selon ta méthode d'authentification)
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy([]); // prends le premier user, adapte au besoin

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/space/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body'); // tu peux être plus précis : .card, .space-title etc selon ton template
        $this->assertSelectorTextContains('h1', ''); // adapte le texte selon ton template
    }
}
