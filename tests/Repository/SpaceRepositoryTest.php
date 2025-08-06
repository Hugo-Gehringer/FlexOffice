<?php

use App\Entity\Desk;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\Address;
use App\Repository\SpaceRepository;
use App\Tests\DatabaseTestCase;
use App\Factory\AddressFactory;
use App\Factory\UserFactory;
use App\Factory\SpaceFactory;
use App\Factory\DeskFactory;


class SpaceRepositoryTest extends DatabaseTestCase
{
    private SpaceRepository $spaceRepository;
    private Desk $testDesk;
    private User $testUser;
    private Space $testSpace;
    private Address $testAddress;

    protected function setUp(): void
    {
        parent::setUp();
        $this->spaceRepository = $this->em->getRepository(Space::class);

        // Créer des entités de test
        $this->createTestEntities();
    }

    private function createTestEntities(): void
    {
        $this->testAddress = AddressFactory::createOne([
            'street' => '123 Test Street',
            'city' => 'Test City',
            'postalCode' => '12345',
            'country' => 'France'
        ])->_real();

        $this->testUser = UserFactory::createOne([
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
            'password' => 'password',
            'roles' => ['ROLE_USER']
        ])->_real();

        $this->testSpace = SpaceFactory::createOne([
            'name' => 'Espace Test',
            'description' => 'Espace de test pour les tests unitaires',
            'host' => $this->testUser,
            'address' => $this->testAddress
        ])->_real();

        $this->testDesk = DeskFactory::createOne([
            'name' => 'Bureau Test',
            'description' => 'Bureau pour les tests',
            'type' => Desk::DESK_TYPE_STANDARD,
            'capacity' => 1,
            'pricePerDay' => 25.0,
            'isAvailable' => true,
            'space' => $this->testSpace
        ])->_real();
    }

    public function testFindByHost(): void
    {
        $host = UserFactory::createOne([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_USER']
        ])->_real();

        $space1 = SpaceFactory::createOne([
            'name' => 'Space 1',
            'description' => 'Description for Space 1',
            'host' => $host
        ])->_real();

        $space2 = SpaceFactory::createOne([
            'name' => 'Space 2',
            'description' => 'Description for Space 2',
            'host' => $host
        ])->_real();

        $spaces = $this->spaceRepository->findByHost($host);

        $this->assertCount(2, $spaces);
        $this->assertEquals($space2->getId(), $spaces[0]->getId()); // Ordered by ID DESC
        $this->assertEquals($space1->getId(), $spaces[1]->getId());
    }

    public function testFindBySearchQuery(): void
    {
        $host = UserFactory::createOne([
            'firstName' => 'Alice',
            'lastName' => 'Smith',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_USER']
        ])->_real();

        $space = SpaceFactory::createOne([
            'name' => 'Beautiful Space',
            'description' => 'A wonderful place to stay',
            'host' => $host,
            'address' => AddressFactory::createOne([
                'city' => 'Paris',
                'street' => '123 Main St',
                'postalCode' => '75001',
                'country' => 'France'
            ])
        ])->_real();

        // Test search by space name
        $spaces = $this->spaceRepository->findBySearchQuery('Beautiful');
        $this->assertCount(1, $spaces);
        $this->assertEquals('Beautiful Space', $spaces[0]->getName());

        // Test search by description
        $spaces = $this->spaceRepository->findBySearchQuery('wonderful');
        $this->assertCount(1, $spaces);

        // Test search by host firstname
        $spaces = $this->spaceRepository->findBySearchQuery('Alice');
        $this->assertCount(1, $spaces);

        // Test search by host lastname
        $spaces = $this->spaceRepository->findBySearchQuery('Smith');
        $this->assertCount(1, $spaces);

        // Test search by city
        $spaces = $this->spaceRepository->findBySearchQuery('Paris');
        $this->assertCount(1, $spaces);

        // Test no results
        $spaces = $this->spaceRepository->findBySearchQuery('nonexistent');
        $this->assertCount(0, $spaces);
    }
}