<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Space;
use App\Entity\Address;
use App\Entity\Favorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FavoriteControllerTest extends WebTestCase
{
    private function createTestData(EntityManagerInterface $entityManager): array
    {
        // Create test user
        $testUser = new User();
        $testUser->setEmail('test@example.com');
        $testUser->setPassword('hashedpassword');
        $testUser->setFirstname('Test');
        $testUser->setLastname('User');
        $testUser->setRoles(['ROLE_GUEST']);
        $testUser->setIsVerified(true);

        // Create test address
        $address = new Address();
        $address->setStreet('123 Test Street');
        $address->setCity('Test City');
        $address->setPostalCode('12345');
        $address->setCountry('France');

        // Create test space
        $testSpace = new Space();
        $testSpace->setName('Test Space');
        $testSpace->setDescription('A test space for favorites');
        $testSpace->setHost($testUser);
        $testSpace->setAddress($address);

        $entityManager->persist($testUser);
        $entityManager->persist($address);
        $entityManager->persist($testSpace);
        $entityManager->flush();

        return ['user' => $testUser, 'space' => $testSpace, 'address' => $address];
    }

    private function cleanupTestData(EntityManagerInterface $entityManager, array $testData): void
    {
        // Clean up test data
        $favorites = $entityManager->getRepository(Favorite::class)->findBy(['user' => $testData['user']]);
        foreach ($favorites as $favorite) {
            $entityManager->remove($favorite);
        }

        $entityManager->remove($testData['space']);
        $entityManager->remove($testData['address']);
        $entityManager->remove($testData['user']);
        $entityManager->flush();
    }

    public function testFavoritesIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/favorites/');

        $this->assertResponseRedirects('/login');
    }

    public function testFavoritesIndexShowsEmptyStateWhenNoFavorites(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $testData = $this->createTestData($entityManager);

        $client->loginUser($testData['user']);

        $crawler = $client->request('GET', '/favorites/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Aucun favori');
        $this->assertSelectorTextContains('p', 'Vous n\'avez pas encore ajouté d\'espaces à vos favoris.');

        $this->cleanupTestData($entityManager, $testData);
    }

    public function testToggleFavoriteAddsSpaceToFavorites(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $testData = $this->createTestData($entityManager);

        $client->loginUser($testData['user']);

        $client->request('POST', '/favorites/toggle/' . $testData['space']->getId(), [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('added', $response['action']);
        $this->assertStringContainsString('ajouté à vos favoris', $response['message']);

        // Verify favorite was created in database
        $favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $testData['user'],
            'space' => $testData['space']
        ]);
        $this->assertNotNull($favorite);

        $this->cleanupTestData($entityManager, $testData);
    }

    public function testToggleFavoriteRemovesSpaceFromFavorites(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $testData = $this->createTestData($entityManager);

        // First add the space to favorites
        $favorite = new Favorite();
        $favorite->setUser($testData['user']);
        $favorite->setSpace($testData['space']);
        $entityManager->persist($favorite);
        $entityManager->flush();

        $client->loginUser($testData['user']);

        $client->request('POST', '/favorites/toggle/' . $testData['space']->getId(), [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('removed', $response['action']);
        $this->assertStringContainsString('retiré de vos favoris', $response['message']);

        // Verify favorite was removed from database
        $favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $testData['user'],
            'space' => $testData['space']
        ]);
        $this->assertNull($favorite);

        $this->cleanupTestData($entityManager, $testData);
    }

    public function testFavoritesIndexShowsFavoriteSpaces(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $testData = $this->createTestData($entityManager);

        // Add the space to favorites
        $favorite = new Favorite();
        $favorite->setUser($testData['user']);
        $favorite->setSpace($testData['space']);
        $entityManager->persist($favorite);
        $entityManager->flush();

        $client->loginUser($testData['user']);

        $crawler = $client->request('GET', '/favorites/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mes Favoris');
        $this->assertSelectorTextContains('h2', $testData['space']->getName());
        $this->assertSelectorExists('.favorite-btn[data-favorited="true"]');

        $this->cleanupTestData($entityManager, $testData);
    }

    public function testToggleFavoriteRequiresAuthentication(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $testData = $this->createTestData($entityManager);

        $client->request('POST', '/favorites/toggle/' . $testData['space']->getId());

        $this->assertResponseRedirects('/login');

        $this->cleanupTestData($entityManager, $testData);
    }
}
