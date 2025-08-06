<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Desk;
use App\Entity\Space;
use App\Entity\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationTest extends KernelTestCase
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

    private function makeValidReservation(): Reservation
    {
        $user = new User();
        $user->setEmail('guest@example.com');
        $user->setPassword('password');
        $user->setFirstname('Jane');
        $user->setLastname('Doe');

        $host = new User();
        $host->setEmail('host@example.com');
        $host->setPassword('password');
        $host->setFirstname('John');
        $host->setLastname('Doe');

        $address = new Address();
        $address->setStreet('1 Rue du Test');
        $address->setPostalCode('34000');
        $address->setCity('Montpellier');
        $address->setCountry('France');

        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Espace de test pour les réservations');
        $space->setHost($host);
        $space->setAddress($address);

        $desk = new Desk();
        $desk->setName('Bureau Test');
        $desk->setType(Desk::DESK_TYPE_STANDARD);
        $desk->setDescription('Un bureau standard');
        $desk->setPricePerDay(25.50);
        $desk->setCapacity(1);
        $desk->setIsAvailable(true);
        $desk->setSpace($space);

        $reservation = new Reservation();
        $reservation->setGuest($user);
        $reservation->setDesk($desk);
        $reservation->setReservationDate(new \DateTime('2025-08-01 10:00:00'));
        $reservation->setStatus(Reservation::STATUS_PENDING);

        return $reservation;
    }

    public function testValidReservationIsPersisted(): void
    {
        $reservation = $this->makeValidReservation();

        $this->em->persist($reservation->getGuest());
        $this->em->persist($reservation->getDesk()->getSpace()->getHost());
        $this->em->persist($reservation->getDesk()->getSpace()->getAddress());
        $this->em->persist($reservation->getDesk()->getSpace());
        $this->em->persist($reservation->getDesk());
        $this->em->persist($reservation);
        $this->em->flush();

        $repo = $this->em->getRepository(Reservation::class);
        $found = $repo->findOneBy(['guest' => $reservation->getGuest()]);

        $this->assertNotNull($found);
        $this->assertEquals('guest@example.com', $found->getGuest()->getEmail());
        $this->assertEquals('Bureau Test', $found->getDesk()->getName());
        $this->assertEquals(Reservation::STATUS_PENDING, $found->getStatus());
    }

    public function testReservationConstants(): void
    {
        $this->assertEquals(0, Reservation::STATUS_PENDING);
        $this->assertEquals(1, Reservation::STATUS_CONFIRMED);
        $this->assertEquals(2, Reservation::STATUS_CANCELLED);

        $expectedStatuses = [
            0 => ' En attente',
            1 => ' Confirmé',
            2 => ' Annulé',
        ];

        $this->assertEquals($expectedStatuses, Reservation::RESERVATION_STATUSES);
    }

    public function testGuestRelation(): void
    {
        $reservation = $this->makeValidReservation();
        $guest = $reservation->getGuest();

        // Utiliser addReservation pour établir correctement la relation bidirectionnelle
        $guest->addReservation($reservation);

        $this->assertSame($guest, $reservation->getGuest());
        $this->assertContains($reservation, $guest->getReservations());
    }

    public function testDeskRelation(): void
    {
        $reservation = $this->makeValidReservation();
        $desk = $reservation->getDesk();

        // Utiliser addReservation pour établir correctement la relation bidirectionnelle
        $desk->addReservation($reservation);

        $this->assertSame($desk, $reservation->getDesk());
        $this->assertContains($reservation, $desk->getReservations());
    }

    public function testStatusTransition(): void
    {
        $reservation = $this->makeValidReservation();

        $reservation->setStatus(Reservation::STATUS_CONFIRMED);
        $this->assertEquals(Reservation::STATUS_CONFIRMED, $reservation->getStatus());

        $reservation->setStatus(Reservation::STATUS_CANCELLED);
        $this->assertEquals(Reservation::STATUS_CANCELLED, $reservation->getStatus());
    }

    public function testReservationDateHandling(): void
    {
        $reservation = $this->makeValidReservation();
        $testDate = new \DateTime('2025-12-25 14:30:00');

        $reservation->setReservationDate($testDate);
        $this->assertEquals($testDate, $reservation->getReservationDate());

        $reservation->setReservationDate(null);
        $this->assertNull($reservation->getReservationDate());
    }

    public function testGettersAndSetters(): void
    {
        $reservation = new Reservation();

        $this->assertNull($reservation->getId());
        $this->assertNull($reservation->getGuest());
        $this->assertNull($reservation->getDesk());
        $this->assertNull($reservation->getReservationDate());
        $this->assertNull($reservation->getStatus());
    }
}
