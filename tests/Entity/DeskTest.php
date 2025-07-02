<?php

namespace App\Tests\Entity;

use App\Entity\Desk;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Reservation;
use App\Entity\Equipment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeskTest extends KernelTestCase
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

    private function makeValidDesk(): Desk
    {
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
        $space->setDescription('Espace de test pour les bureaux');
        $space->setHost($user);
        $space->setAddress($address);

        $desk = new Desk();
        $desk->setName('Bureau Test');
        $desk->setType(Desk::DESK_TYPE_STANDARD);
        $desk->setDescription('Un bureau standard pour travailler');
        $desk->setPricePerDay(25.50);
        $desk->setCapacity(1);
        $desk->setIsAvailable(true);
        $desk->setSpace($space);

        return $desk;
    }

    public function testValidDeskIsPersisted(): void
    {
        $desk = $this->makeValidDesk();

        $this->em->persist($desk->getSpace()->getHost());
        $this->em->persist($desk->getSpace()->getAddress());
        $this->em->persist($desk->getSpace());
        $this->em->persist($desk);
        $this->em->flush();

        $repo = $this->em->getRepository(Desk::class);
        $found = $repo->findOneBy(['name' => 'Bureau Test']);

        $this->assertNotNull($found);
        $this->assertEquals('Bureau Test', $found->getName());
        $this->assertEquals(Desk::DESK_TYPE_STANDARD, $found->getType());
        $this->assertEquals(25.50, $found->getPricePerDay());
        $this->assertEquals(1, $found->getCapacity());
        $this->assertTrue($found->isAvailable());
    }

    public function testDeskConstants(): void
    {
        $this->assertEquals(0, Desk::DESK_TYPE_STANDARD);
        $this->assertEquals(1, Desk::DESK_TYPE_PRIVATE_OFFICE);
        $this->assertEquals(2, Desk::DESK_TYPE_MEETING_ROOM);
        $this->assertEquals(3, Desk::DESK_TYPE_CONFERENCE_ROOM);

        $expectedTypes = [
            0 => 'Bureau standard',
            1 => 'Bureau privé',
            2 => 'Salle de réunion',
            3 => ' Salle de conférence',
        ];

        $this->assertEquals($expectedTypes, Desk::DESK_TYPES);
    }

    public function testSpaceRelation(): void
    {
        $desk = $this->makeValidDesk();
        $space = $desk->getSpace();

        // Utiliser addDesk() pour établir correctement la relation bidirectionnelle
        $space->addDesk($desk);

        $this->assertSame($space, $desk->getSpace());
        $this->assertContains($desk, $space->getDesks());
    }

    public function testReservationsRelation(): void
    {
        $desk = $this->makeValidDesk();
        $reservation = new Reservation();

        $desk->addReservation($reservation);

        $this->assertCount(1, $desk->getReservations());
        $this->assertSame($desk, $reservation->getDesk());

        $desk->removeReservation($reservation);

        $this->assertCount(0, $desk->getReservations());
        $this->assertNull($reservation->getDesk());
    }

    public function testEquipmentRelation(): void
    {
        $desk = $this->makeValidDesk();
        $equipment = new Equipment();
        $equipment->setName('Écran');
        $equipment->setDescription('Écran de bureau'); // Ajout description requise

        // Utiliser la méthode addEquipment du desk qui gère la relation bidirectionnelle
        $desk->addEquipment($equipment);

        $this->assertCount(1, $desk->getEquipments());
        $this->assertContains($desk, $equipment->getDesks());

        $desk->removeEquipment($equipment);

        $this->assertCount(0, $desk->getEquipments());
        $this->assertNotContains($desk, $equipment->getDesks());
    }

    public function testPriceValidation(): void
    {
        $desk = $this->makeValidDesk();

        $desk->setPricePerDay(-5.0);
        $errors = $this->validator->validate($desk);
        $this->assertGreaterThan(0, count($errors), 'Negative price should trigger validation error.');
    }

    public function testCapacityValidation(): void
    {
        $desk = $this->makeValidDesk();

        $desk->setCapacity(0);
        $errors = $this->validator->validate($desk);
        $this->assertGreaterThan(0, count($errors), 'Zero capacity should trigger validation error.');
    }

    public function testGettersAndSetters(): void
    {
        $desk = new Desk();

        $this->assertNull($desk->getId());
        $this->assertNull($desk->getName());
        $this->assertNull($desk->getType());
        $this->assertNull($desk->getDescription());
        $this->assertNull($desk->getPricePerDay());
        $this->assertNull($desk->getCapacity());
        $this->assertNull($desk->isAvailable());
        $this->assertNull($desk->getSpace());
        $this->assertCount(0, $desk->getReservations());
        $this->assertCount(0, $desk->getEquipments());
    }
}
