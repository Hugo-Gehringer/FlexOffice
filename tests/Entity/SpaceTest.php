<?php

namespace App\Tests\Entity;

use App\Entity\Space;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Desk;
use App\Entity\Availability;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpaceTest extends KernelTestCase
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

    private function makeUser(): User
    {
        $user = new User();
        $user->setEmail('host@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName('John');
        $user->setLastName('Doe');
        return $user;
    }

    private function makeAddress(): Address
    {
        $address = new Address();
        $address->setStreet('1 Rue du Test');
        $address->setPostalCode('34000');
        $address->setCity('Montpellier');
        $address->setCountry('France');
        return $address;
    }

    private function makeValidSpace(): Space
    {
        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Un espace parfait pour travailler au calme et à la lumière.');
        $space->setHost($this->makeUser());
        $space->setAddress($this->makeAddress());
        return $space;
    }

    public function testValidSpaceIsPersisted()
    {
        $space = $this->makeValidSpace();

        $this->em->persist($space->getHost());
        $this->em->persist($space->getAddress());
        $this->em->persist($space);
        $this->em->flush();

        $repo = $this->em->getRepository(Space::class);
        $found = $repo->findOneBy(['name' => 'Test Space']);

        $this->assertNotNull($found);
        $this->assertEquals('Test Space', $found->getName());
        $this->assertEquals('host@example.com', $found->getHost()->getEmail());
        $this->assertEquals('Montpellier', $found->getAddress()->getCity());
    }

    public function testNameIsRequired()
    {
        $space = $this->makeValidSpace();
        $space->setName('');

        $errors = $this->validator->validate($space);
        $this->assertGreaterThan(0, count($errors), 'Blank name should trigger validation error.');
    }

    public function testNameMinMaxLength()
    {
        $space = $this->makeValidSpace();

        $space->setName('ab');
        $errors = $this->validator->validate($space);
        $this->assertGreaterThan(0, count($errors), 'Name <3 chars should trigger validation.');

        $space->setName(str_repeat('a', 61));
        $errors = $this->validator->validate($space);
        $this->assertGreaterThan(0, count($errors), 'Name >60 chars should trigger validation.');
    }

    public function testDescriptionIsRequiredAndMinLength()
    {
        $space = $this->makeValidSpace();

        $space->setDescription('');
        $errors = $this->validator->validate($space);
        $this->assertGreaterThan(0, count($errors), 'Blank description should trigger validation.');

        $space->setDescription('short');
        $errors = $this->validator->validate($space);
        $this->assertGreaterThan(0, count($errors), 'Description <10 chars should trigger validation.');
    }

    public function testDeskRelation()
    {
        $space = $this->makeValidSpace();
        $desk = new Desk();
        $desk->setName('Bureau 1');
        $desk->setSpace($space);

        $space->addDesk($desk);

        $this->assertCount(1, $space->getDesks());
        $this->assertSame($space, $desk->getSpace());

        $space->removeDesk($desk);

        $this->assertCount(0, $space->getDesks());
        $this->assertNull($desk->getSpace());
    }

    public function testAvailabilityRelation()
    {
        $space = $this->makeValidSpace();
        $availability = new Availability();
        $availability->setSpace($space);

        $space->setAvailability($availability);

        $this->assertSame($availability, $space->getAvailability());
        $this->assertSame($space, $availability->getSpace());

        $space->setAvailability(null);
        $this->assertNull($space->getAvailability());
    }
}
