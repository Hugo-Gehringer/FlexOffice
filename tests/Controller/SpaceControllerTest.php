<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Factory\SpaceFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SpaceControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    public function testIndexRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/space/');
        $this->assertResponseRedirects('/login');
    }

    public function testIndexDisplaysSpacesForLoggedInUser(): void
    {
        $client = static::createClient();

        // Créer un utilisateur de test avec la factory
        $testUser = UserFactory::createOne();

        // Connecter l'utilisateur créé
        $client->loginUser($testUser->_real());

        // Effectuer la requête
        $client->request('GET', '/space/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Espaces disponibles');
    }

    public function testNewSpaceFormIsDisplayed(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $client->loginUser($user->_real());

        $crawler = $client->request('GET', '/space/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewSpaceRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/space/new');
        $this->assertResponseRedirects('/login');
    }

    public function testCreateNewSpace(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $client->loginUser($user->_real());

        $crawler = $client->request('GET', '/space/new');

        $form = $crawler->selectButton('Créer espace')->form([
            'space_form[name]' => 'New Test Space',
            'space_form[description]' => 'New Test Description',
            'space_form[address][street]' => '456 New Street',
            'space_form[address][city]' => 'New City',
            'space_form[address][postalCode]' => '67890',
            'space_form[address][country]' => 'France',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/space/', $client->getResponse()->headers->get('Location'));
    }

    public function testMySpacesRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/space/my-spaces');
        $this->assertResponseRedirects('/login');
    }

    public function testMySpacesDisplaysUserSpaces(): void
    {
        $client = static::createClient();


        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user, 'name' => 'Test Space']);

        $client->loginUser($user->_real());

        $crawler = $client->request('GET', '/space/my-spaces');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test Space', $client->getResponse()->getContent());
    }

    public function testShowSpaceRequiresAuthentication(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->request('GET', '/space/' . $space->getId());
        $this->assertResponseRedirects('/login');
    }

    public function testShowSpaceDisplaysSpaceDetails(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne([
            'host' => $user,
            'name' => 'Test Space',
            'description' => 'Test Description'
        ]);

        $client->loginUser($user->_real());

        $crawler = $client->request('GET', '/space/' . $space->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test Space', $client->getResponse()->getContent());
        $this->assertStringContainsString('Test Description', $client->getResponse()->getContent());
    }

    public function testEditSpaceRequiresAuthentication(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->request('GET', '/space/' . $space->getId() . '/edit');
        $this->assertResponseRedirects('/login');
    }

    public function testEditSpaceByOwner(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->loginUser($user->_real());

        $crawler = $client->request('GET', '/space/' . $space->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditSpaceDeniedForNonOwner(): void
    {
        $client = static::createClient();

        $owner = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $owner]);

        $otherUser = UserFactory::createOne([
            'email' => 'user_' . uniqid() . '@example.com',
            'firstname' => 'Jane',
            'lastname' => 'Smith'
        ]);

        $client->loginUser($otherUser->_real());

        $client->request('GET', '/space/' . $space->getId() . '/edit');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditSpaceAllowedForAdmin(): void
    {
        $client = static::createClient();

        $owner = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $owner]);

        $admin = UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
            'email' => 'admin@example.com'
        ]);

        $client->loginUser($admin->_real());

        $crawler = $client->request('GET', '/space/' . $space->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testDeleteSpaceRequiresAuthentication(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->request('POST', '/space/' . $space->getId() . '/delete');
        $this->assertResponseRedirects('/login');
    }

    public function testDeleteSpaceByOwner(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->loginUser($user->_real());

        $client->request('POST', '/space/' . $space->getId() . '/delete');

        $this->assertResponseRedirects('/space/my-spaces');
    }

    public function testDeleteSpaceDeniedForNonOwner(): void
    {
        $client = static::createClient();

        $owner = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $owner]);

        $otherUser = UserFactory::createOne([
            'email' => 'user_' . uniqid() . '@example.com',
            'firstname' => 'Jane',
            'lastname' => 'Smith'
        ]);

        $client->loginUser($otherUser->_real());

        $client->request('POST', '/space/' . $space->getId() . '/delete');

        $this->assertResponseStatusCodeSame(403);
    }

}