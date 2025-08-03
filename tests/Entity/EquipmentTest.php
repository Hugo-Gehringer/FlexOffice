<?php

namespace App\Tests\Entity;

use App\Entity\Equipment;
use App\Entity\Desk;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EquipmentTest extends KernelTestCase
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

    private function makeValidEquipment(): Equipment
    {
        $equipment = new Equipment();
        $equipment->setName('Écran 27 pouces');
        $equipment->setDescription('Écran LED haute définition pour une meilleure productivité');
        return $equipment;
    }

    public function testValidEquipmentIsPersisted(): void
    {
        $equipment = $this->makeValidEquipment();

        $this->em->persist($equipment);
        $this->em->flush();

        $repo = $this->em->getRepository(Equipment::class);
        $found = $repo->findOneBy(['name' => 'Écran 27 pouces']);

        $this->assertNotNull($found);
        $this->assertEquals('Écran 27 pouces', $found->getName());
        $this->assertEquals('Écran LED haute définition pour une meilleure productivité', $found->getDescription());
    }

    public function testNameIsRequired(): void
    {
        $equipment = $this->makeValidEquipment();
        $equipment->setName('');

        $errors = $this->validator->validate($equipment);
        $this->assertGreaterThan(0, count($errors), 'Blank name should trigger validation error.');
    }

    public function testDescriptionIsRequired(): void
    {
        $equipment = $this->makeValidEquipment();
        $equipment->setDescription('');

        $errors = $this->validator->validate($equipment);
        $this->assertGreaterThan(0, count($errors), 'Blank description should trigger validation error.');
    }

    public function testDeskRelation(): void
    {
        $equipment = $this->makeValidEquipment();

        $user = new User();
        $user->setEmail('host@example.com');
        $user->setPassword('password');
        $user->setFirstname('John');
        $user->setLastname('Doe');

        $address = new Address();
        $address->setStreet('1 Rue du Test');
        $address->setPostalCode('34000');
        $address->setCity('Montpellier');
        $address->setCountry('France');

        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Espace de test pour équipements');
        $space->setHost($user);
        $space->setAddress($address);

        $desk = new Desk();
        $desk->setName('Bureau Test');
        $desk->setType(Desk::DESK_TYPE_STANDARD);
        $desk->setDescription('Un bureau standard');
        $desk->setPricePerDay(25.50);
        $desk->setCapacity(1);
        $desk->setIsAvailable(true);
        $desk->setSpace($space);

        $equipment->addDesk($desk);

        $this->assertCount(1, $equipment->getDesks());
        $this->assertContains($equipment, $desk->getEquipments());

        $equipment->removeDesk($desk);

        $this->assertCount(0, $equipment->getDesks());
        $this->assertNotContains($equipment, $desk->getEquipments());
    }

    public function testNameMaxLength(): void
    {
        $equipment = $this->makeValidEquipment();
        $equipment->setName(str_repeat('a', 61));

        $errors = $this->validator->validate($equipment);
        $this->assertGreaterThan(0, count($errors), 'Name >60 chars should trigger validation error.');
    }

    public function testDescriptionMaxLength(): void
    {
        $equipment = $this->makeValidEquipment();
        $equipment->setDescription(str_repeat('a', 256));

        $errors = $this->validator->validate($equipment);
        $this->assertGreaterThan(0, count($errors), 'Description >255 chars should trigger validation error.');
    }

    public function testGettersAndSetters(): void
    {
        $equipment = new Equipment();

        $this->assertNull($equipment->getId());
        $this->assertNull($equipment->getName());
        $this->assertNull($equipment->getDescription());
        $this->assertCount(0, $equipment->getDesks());
    }
}
