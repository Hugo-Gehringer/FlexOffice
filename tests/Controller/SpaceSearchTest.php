<?php

namespace App\Tests\Controller;

use App\Factory\SpaceFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SpaceSearchTest extends WebTestCase
{
    use ResetDatabase, Factories;

    public function testSpaceSearchByName(): void
    {
        $client = static::createClient();

        // Create a user and login
        $user = UserFactory::createOne();
        $client->loginUser($user->_real());

        // Create test spaces
        $space1 = SpaceFactory::createOne(['name' => 'Bureau moderne']);
        $space2 = SpaceFactory::createOne(['name' => 'Espace coworking']);
        $space3 = SpaceFactory::createOne(['name' => 'Salle de réunion']);

        // Test search by name
        $crawler = $client->request('GET', '/space/?search=Bureau');
        $this->assertResponseIsSuccessful();

        // Debug: Check the actual content
        $content = $client->getResponse()->getContent();

        // Should find the space with "Bureau" in the name
        $this->assertStringContainsString('Bureau moderne', $content);
        $this->assertStringNotContainsString('Espace coworking', $content);
        $this->assertStringNotContainsString('Salle de réunion', $content);
    }

    public function testSpaceSearchAPI(): void
    {
        $client = static::createClient();

        // Create a user and login
        $user = UserFactory::createOne();
        $client->loginUser($user->_real());

        // Create test spaces
        $space1 = SpaceFactory::createOne(['name' => 'Bureau moderne']);
        $space2 = SpaceFactory::createOne(['name' => 'Espace coworking']);

        // Test API search
        $client->request('GET', '/space/search?q=Bureau');
        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Bureau moderne', $data[0]['name']);
    }

    public function testSpaceSearchEmpty(): void
    {
        $client = static::createClient();

        // Create a user and login
        $user = UserFactory::createOne();
        $client->loginUser($user->_real());

        // Test search with no results
        $crawler = $client->request('GET', '/space/?search=NonExistentSpace');
        $this->assertResponseIsSuccessful();

        // Should show no results message
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('Aucun résultat trouvé', $content);
    }

    public function testSpaceSearchRequiresAuthentication(): void
    {
        $client = static::createClient();

        // Test without authentication
        $client->request('GET', '/space/search?q=test');
        $this->assertResponseRedirects();
    }
}