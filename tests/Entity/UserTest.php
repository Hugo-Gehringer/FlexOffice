<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Space;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->validator = $container->get(ValidatorInterface::class);

        // Schema reset (in-memory SQLite)
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }

    private function makeValidUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setRoles(['ROLE_USER']);
        return $user;
    }

    public function testValidUserIsPersisted(): void
    {
        $user = $this->makeValidUser();

        $this->em->persist($user);
        $this->em->flush();

        $repo = $this->em->getRepository(User::class);
        $found = $repo->findOneBy(['email' => 'test@example.com']);

        $this->assertNotNull($found);
        $this->assertEquals('test@example.com', $found->getEmail());
        $this->assertEquals('John', $found->getFirstname());
        $this->assertEquals('Doe', $found->getLastname());
    }

    public function testEmailIsRequired(): void
    {
        $user = $this->makeValidUser();
        $user->setEmail('');

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), 'Blank email should trigger validation error.');
    }

    public function testEmailMustBeUnique(): void
    {
        $user1 = $this->makeValidUser();
        $user1->setEmail('unique@example.com');

        $this->em->persist($user1);
        $this->em->flush();

        $user2 = $this->makeValidUser();
        $user2->setEmail('unique@example.com');

        $errors = $this->validator->validate($user2);
        $this->assertGreaterThan(0, count($errors), 'Duplicate email should trigger validation error.');
    }

    public function testUserIdentifier(): void
    {
        $user = $this->makeValidUser();
        $user->setEmail('identifier@example.com');

        $this->assertEquals('identifier@example.com', $user->getUserIdentifier());
    }

    public function testRoles(): void
    {
        $user = $this->makeValidUser();

        // Test default roles
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);

        // Test setting admin role
        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles); // Always included
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testPasswordHandling(): void
    {
        $user = $this->makeValidUser();
        $user->setPassword('hashed_password');

        $this->assertEquals('hashed_password', $user->getPassword());

        // Test eraseCredentials doesn't break anything
        $user->eraseCredentials();
        $this->assertEquals('hashed_password', $user->getPassword());
    }

    public function testIsVerified(): void
    {
        $user = $this->makeValidUser();

        // Default should be false
        $this->assertFalse($user->isVerified());

        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testSpacesHostedRelation(): void
    {
        $user = $this->makeValidUser();
        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Description test pour espace');

        $user->addSpacesHosted($space);

        $this->assertCount(1, $user->getSpacesHosted());
        $this->assertSame($user, $space->getHost());

        $user->removeSpacesHosted($space);

        $this->assertCount(0, $user->getSpacesHosted());
        $this->assertNull($space->getHost());
    }

    public function testReservationsRelation(): void
    {
        $user = $this->makeValidUser();
        $reservation = new Reservation();

        $user->addReservation($reservation);

        $this->assertCount(1, $user->getReservations());
        $this->assertSame($user, $reservation->getGuest());

        $user->removeReservation($reservation);

        $this->assertCount(0, $user->getReservations());
        $this->assertNull($reservation->getGuest());
    }

    public function testFirstnameValidation(): void
    {
        $user = $this->makeValidUser();

        $user->setFirstname('');
        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), 'Blank firstname should trigger validation error.');

        $user->setFirstname(str_repeat('a', 51));
        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), 'Firstname >50 chars should trigger validation error.');
    }

    public function testLastnameValidation(): void
    {
        $user = $this->makeValidUser();

        $user->setLastname('');
        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), 'Blank lastname should trigger validation error.');

        $user->setLastname(str_repeat('a', 51));
        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), 'Lastname >50 chars should trigger validation error.');
    }

    public function testGettersAndSetters(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getFirstname());
        $this->assertNull($user->getLastname());
        $this->assertNull($user->getPassword());
        $this->assertFalse($user->isVerified());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}
