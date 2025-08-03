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
    private EntityManagerInterface $entityManager;
    private User $testUser;
    private Space $testSpace;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        
        // Create test user
        $this->testUser = new User();
        $this->testUser->setEmail('test@example.com');
        $this->testUser->setPassword('hashedpassword');
        $this->testUser->setFirstname('Test');
        $this->testUser->setLastname('User');
        $this->testUser->setRoles(['ROLE_GUEST']);
        $this->testUser->setIsVerified(true);
        
        // Create test address
        $address = new Address();
        $address->setStreet('123 Test Street');
        $address->setCity('Test City');
        $address->setPostalCode('12345');
        $address->setCountry('France');
        
        // Create test space
        $this->testSpace = new Space();
        $this->testSpace->setName('Test Space');
        $this->testSpace->setDescription('A test space for favorites');
        $this->testSpace->setHost($this->testUser);
        $this->testSpace->setAddress($address);
        
        $this->entityManager->persist($this->testUser);
        $this->entityManager->persist($address);
        $this->entityManager->persist($this->testSpace);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $favorites = $this->entityManager->getRepository(Favorite::class)->findBy(['user' => $this->testUser]);
        foreach ($favorites as $favorite) {
            $this->entityManager->remove($favorite);
        }
        
        $this->entityManager->remove($this->testSpace);
        $this->entityManager->remove($this->testSpace->getAddress());
        $this->entityManager->remove($this->testUser);
        $this->entityManager->flush();
        
        parent::tearDown();
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
        $client->loginUser($this->testUser);
        
        $crawler = $client->request('GET', '/favorites/');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Aucun favori');
        $this->assertSelectorTextContains('p', 'Vous n\'avez pas encore ajouté d\'espaces à vos favoris.');
    }

    public function testToggleFavoriteAddsSpaceToFavorites(): void
    {
        $client = static::createClient();
        $client->loginUser($this->testUser);
        
        $client->request('POST', '/favorites/toggle/' . $this->testSpace->getId(), [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('added', $response['action']);
        $this->assertStringContainsString('ajouté à vos favoris', $response['message']);
        
        // Verify favorite was created in database
        $favorite = $this->entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $this->testUser,
            'space' => $this->testSpace
        ]);
        $this->assertNotNull($favorite);
    }

    public function testToggleFavoriteRemovesSpaceFromFavorites(): void
    {
        // First add the space to favorites
        $favorite = new Favorite();
        $favorite->setUser($this->testUser);
        $favorite->setSpace($this->testSpace);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();
        
        $client = static::createClient();
        $client->loginUser($this->testUser);
        
        $client->request('POST', '/favorites/toggle/' . $this->testSpace->getId(), [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('removed', $response['action']);
        $this->assertStringContainsString('retiré de vos favoris', $response['message']);
        
        // Verify favorite was removed from database
        $favorite = $this->entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $this->testUser,
            'space' => $this->testSpace
        ]);
        $this->assertNull($favorite);
    }

    public function testFavoritesIndexShowsFavoriteSpaces(): void
    {
        // Add the space to favorites
        $favorite = new Favorite();
        $favorite->setUser($this->testUser);
        $favorite->setSpace($this->testSpace);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();
        
        $client = static::createClient();
        $client->loginUser($this->testUser);
        
        $crawler = $client->request('GET', '/favorites/');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mes Favoris');
        $this->assertSelectorTextContains('h2', $this->testSpace->getName());
        $this->assertSelectorExists('.favorite-btn[data-favorited="true"]');
    }

    public function testToggleFavoriteRequiresAuthentication(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/favorites/toggle/' . $this->testSpace->getId());
        
        $this->assertResponseRedirects('/login');
    }
}
