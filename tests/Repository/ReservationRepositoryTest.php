<?php

namespace App\Tests\Repository;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Entity\User;

use App\Entity\Space;
use App\Entity\Address;
use App\Repository\ReservationRepository;
use App\Tests\DatabaseTestCase;
use DateTime;

class ReservationRepositoryTest extends DatabaseTestCase
{
    private ReservationRepository $repository;
    private Desk $testDesk;
    private User $testUser;
    private Space $testSpace;
    private Address $testAddress;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Reservation::class);

        // Créer des entités de test
        $this->createTestEntities();
    }

    private function createTestEntities(): void
    {
        $this->testAddress = new Address();
        $this->testAddress->setStreet('123 Test Street');
        $this->testAddress->setCity('Test City');
        $this->testAddress->setPostalCode('12345');
        $this->testAddress->setCountry('France');
        $this->em->persist($this->testAddress);

        $this->testUser = new User();
        $this->testUser->setEmail('test@example.com');
        $this->testUser->setFirstName('Test');
        $this->testUser->setLastName('User');
        $this->testUser->setPassword('password');
        $this->testUser->setRoles(['ROLE_USER']);
        $this->em->persist($this->testUser);

        $this->testSpace = new Space();
        $this->testSpace->setName('Espace Test');
        $this->testSpace->setDescription('Espace de test pour les tests unitaires');
        $this->testSpace->setHost($this->testUser);
        $this->testSpace->setAddress($this->testAddress);
        $this->em->persist($this->testSpace);

        $this->testDesk = new Desk();
        $this->testDesk->setName('Bureau Test');
        $this->testDesk->setDescription('Bureau pour les tests');
        $this->testDesk->setType(Desk::DESK_TYPE_STANDARD);
        $this->testDesk->setCapacity(1);
        $this->testDesk->setPricePerDay(25.0);
        $this->testDesk->setIsAvailable(true);
        $this->testDesk->setSpace($this->testSpace);
        $this->em->persist($this->testDesk);

        $this->em->flush();
    }

    public function testFindReservationsForDeskOnDate(): void
    {
        $testDate = new DateTime('2024-01-15');

        $reservationSameDay = $this->createReservation($this->testDesk, new DateTime('2024-01-15 10:00:00'), Reservation::STATUS_CONFIRMED);
        $reservationDifferentDay = $this->createReservation($this->testDesk, new DateTime('2024-01-16 10:00:00'), Reservation::STATUS_CONFIRMED);
        $reservationCancelled = $this->createReservation($this->testDesk, new DateTime('2024-01-15 14:00:00'), Reservation::STATUS_CANCELLED);

        $this->em->flush();

        $reservations = $this->repository->findReservationsForDeskOnDate($this->testDesk, $testDate);

        $this->assertCount(1, $reservations);
        $this->assertEquals($reservationSameDay->getId(), $reservations[0]->getId());

        $reservationIds = array_map(fn($r) => $r->getId(), $reservations);
        $this->assertNotContains($reservationDifferentDay->getId(), $reservationIds);
        $this->assertNotContains($reservationCancelled->getId(), $reservationIds);
    }

    public function testFindReservationsForDeskOnDateWithMultipleReservations(): void
    {
        $testDate = new DateTime('2024-01-15');

        // Créer plusieurs réservations le même jour
        $reservation1 = $this->createReservation($this->testDesk, new DateTime('2024-01-15 09:00:00'), Reservation::STATUS_CONFIRMED);
        $reservation2 = $this->createReservation($this->testDesk, new DateTime('2024-01-15 14:00:00'), Reservation::STATUS_PENDING);
        $reservation3 = $this->createReservation($this->testDesk, new DateTime('2024-01-15 16:00:00'), Reservation::STATUS_CONFIRMED);

        $this->em->flush();

        $reservations = $this->repository->findReservationsForDeskOnDate($this->testDesk, $testDate);

        $this->assertCount(3, $reservations);
    }

    public function testFindReservationsForDeskOnDateWithNoReservations(): void
    {
        $testDate = new DateTime('2024-01-15');

        $reservations = $this->repository->findReservationsForDeskOnDate($this->testDesk, $testDate);

        $this->assertCount(0, $reservations);
        $this->assertEmpty($reservations);
    }

    public function testIsDeskAvailableOnDateWhenAvailable(): void
    {
        $testDate = new DateTime('2024-01-15');

        $isAvailable = $this->repository->isDeskAvailableOnDate($this->testDesk, $testDate);

        $this->assertTrue($isAvailable);
    }

    public function testIsDeskAvailableOnDateWhenNotAvailable(): void
    {
        $testDate = new DateTime('2024-01-15');

        // Créer une réservation confirmée
        $this->createReservation($this->testDesk, new DateTime('2024-01-15 10:00:00'), Reservation::STATUS_CONFIRMED);
        $this->em->flush();

        $isAvailable = $this->repository->isDeskAvailableOnDate($this->testDesk, $testDate);

        $this->assertFalse($isAvailable);
    }

    public function testIsDeskAvailableOnDateWithCancelledReservations(): void
    {
        $testDate = new DateTime('2024-01-15');

        $this->createReservation($this->testDesk, new DateTime('2024-01-15 10:00:00'), Reservation::STATUS_CANCELLED);
        $this->createReservation($this->testDesk, new DateTime('2024-01-15 14:00:00'), Reservation::STATUS_CANCELLED);
        $this->em->flush();

        $isAvailable = $this->repository->isDeskAvailableOnDate($this->testDesk, $testDate);

        $this->assertTrue($isAvailable);
    }

    public function testFindReservationsForDeskOnDateWithDifferentDesks(): void
    {
        $testDate = new DateTime('2024-01-15');

        // Créer un autre bureau
        $otherDesk = new Desk();
        $otherDesk->setName('Autre Bureau');
        $otherDesk->setDescription('Autre bureau pour les tests');
        $otherDesk->setType(Desk::DESK_TYPE_STANDARD);
        $otherDesk->setCapacity(1);
        $otherDesk->setPricePerDay(30.0);
        $otherDesk->setIsAvailable(true);
        $otherDesk->setSpace($this->testSpace); // Utiliser le même espace
        $this->em->persist($otherDesk);

        // Créer des réservations pour les deux bureaux
        $reservationDesk1 = $this->createReservation($this->testDesk, new DateTime('2024-01-15 10:00:00'), Reservation::STATUS_CONFIRMED);
        $reservationDesk2 = $this->createReservation($otherDesk, new DateTime('2024-01-15 10:00:00'), Reservation::STATUS_CONFIRMED);

        $this->em->flush();

        $reservationsDesk1 = $this->repository->findReservationsForDeskOnDate($this->testDesk, $testDate);
        $reservationsDesk2 = $this->repository->findReservationsForDeskOnDate($otherDesk, $testDate);

        $this->assertCount(1, $reservationsDesk1);
        $this->assertCount(1, $reservationsDesk2);
        $this->assertEquals($reservationDesk1->getId(), $reservationsDesk1[0]->getId());
        $this->assertEquals($reservationDesk2->getId(), $reservationsDesk2[0]->getId());
    }

    public function testFindReservationsForDeskOnDateEdgeCases(): void
    {
        $testDate = new DateTime('2024-01-15');

        $reservationStartOfDay = $this->createReservation($this->testDesk, new DateTime('2024-01-15 00:00:00'), Reservation::STATUS_CONFIRMED);
        $reservationEndOfDay = $this->createReservation($this->testDesk, new DateTime('2024-01-15 23:59:59'), Reservation::STATUS_CONFIRMED);
        $reservationNextDayStart = $this->createReservation($this->testDesk, new DateTime('2024-01-16 00:00:00'), Reservation::STATUS_CONFIRMED);

        $this->em->flush();

        $reservations = $this->repository->findReservationsForDeskOnDate($this->testDesk, $testDate);

        $this->assertCount(2, $reservations);

        $reservationIds = array_map(fn($r) => $r->getId(), $reservations);
        $this->assertContains($reservationStartOfDay->getId(), $reservationIds);
        $this->assertContains($reservationEndOfDay->getId(), $reservationIds);
        $this->assertNotContains($reservationNextDayStart->getId(), $reservationIds);
    }

    private function createReservation(Desk $desk, DateTime $date, int $status): Reservation
    {
        $reservation = new Reservation();
        $reservation->setDesk($desk);
        $reservation->setGuest($this->testUser);
        $reservation->setReservationDate($date);
        $reservation->setStatus($status);

        $this->em->persist($reservation);

        return $reservation;
    }
}
