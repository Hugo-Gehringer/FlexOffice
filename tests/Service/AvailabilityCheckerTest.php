<?php

namespace App\Tests\Service;

use App\Entity\Availability;
use App\Entity\Desk;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\Address;
use App\Repository\ReservationRepository;
use App\Service\AvailabilityChecker;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AvailabilityCheckerTest extends TestCase
{
    private AvailabilityChecker $availabilityChecker;
    private ReservationRepository|MockObject $reservationRepository;

    protected function setUp(): void
    {
        $this->reservationRepository = $this->createMock(ReservationRepository::class);
        $this->availabilityChecker = new AvailabilityChecker($this->reservationRepository);
    }

    private function createTestDesk(bool $isAvailable = true): Desk
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

        $availability = new Availability();
        $availability->setMonday(true);
        $availability->setTuesday(true);
        $availability->setWednesday(true);
        $availability->setThursday(true);
        $availability->setFriday(true);
        $availability->setSaturday(false);
        $availability->setSunday(false);

        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('Espace de test pour vérifier disponibilité');
        $space->setHost($user);
        $space->setAddress($address);
        $space->setAvailability($availability);

        $desk = new Desk();
        $desk->setName('Bureau Test');
        $desk->setType(Desk::DESK_TYPE_STANDARD);
        $desk->setDescription('Un bureau standard');
        $desk->setPricePerDay(25.50);
        $desk->setCapacity(1);
        $desk->setIsAvailable($isAvailable);
        $desk->setSpace($space);

        return $desk;
    }

    public function testIsDeskAvailableOnDateWhenDeskIsNotAvailable(): void
    {
        $desk = $this->createTestDesk(false); // Desk marked as not available
        $date = new \DateTime('2025-07-01'); // Tuesday

        $result = $this->availabilityChecker->isDeskAvailableOnDate($desk, $date);

        $this->assertFalse($result);
    }

    public function testIsDeskAvailableOnDateWhenDayIsNotAvailable(): void
    {
        $desk = $this->createTestDesk(true);
        $date = new \DateTime('2025-07-06'); // Sunday (not available in test setup)

        $result = $this->availabilityChecker->isDeskAvailableOnDate($desk, $date);

        $this->assertFalse($result);
    }

    public function testIsDeskAvailableOnDateWhenDeskHasReservation(): void
    {
        $desk = $this->createTestDesk(true);
        $date = new \DateTime('2025-07-01'); // Tuesday (available day)

        // Mock repository to return false (desk has reservation)
        $this->reservationRepository
            ->expects($this->once())
            ->method('isDeskAvailableOnDate')
            ->with($desk, $date)
            ->willReturn(false);

        $result = $this->availabilityChecker->isDeskAvailableOnDate($desk, $date);

        $this->assertFalse($result);
    }

    public function testIsDeskAvailableOnDateWhenAllConditionsAreMet(): void
    {
        $desk = $this->createTestDesk(true);
        $date = new \DateTime('2025-07-01'); // Tuesday (available day)

        // Mock repository to return true (no reservations)
        $this->reservationRepository
            ->expects($this->once())
            ->method('isDeskAvailableOnDate')
            ->with($desk, $date)
            ->willReturn(true);

        $result = $this->availabilityChecker->isDeskAvailableOnDate($desk, $date);

        $this->assertTrue($result);
    }

    public function testIsDeskAvailableOnDifferentDaysOfWeek(): void
    {
        $desk = $this->createTestDesk(true);

        // Mock repository to always return true for this test
        $this->reservationRepository
            ->method('isDeskAvailableOnDate')
            ->willReturn(true);

        // Test Monday (available)
        $monday = new \DateTime('2025-06-30');
        $this->assertTrue($this->availabilityChecker->isDeskAvailableOnDate($desk, $monday));

        // Test Tuesday (available)
        $tuesday = new \DateTime('2025-07-01');
        $this->assertTrue($this->availabilityChecker->isDeskAvailableOnDate($desk, $tuesday));

        // Test Wednesday (available)
        $wednesday = new \DateTime('2025-07-02');
        $this->assertTrue($this->availabilityChecker->isDeskAvailableOnDate($desk, $wednesday));

        // Test Thursday (available)
        $thursday = new \DateTime('2025-07-03');
        $this->assertTrue($this->availabilityChecker->isDeskAvailableOnDate($desk, $thursday));

        // Test Friday (available)
        $friday = new \DateTime('2025-07-04');
        $this->assertTrue($this->availabilityChecker->isDeskAvailableOnDate($desk, $friday));

        // Test Saturday (not available)
        $saturday = new \DateTime('2025-07-05');
        $this->assertFalse($this->availabilityChecker->isDeskAvailableOnDate($desk, $saturday));

        // Test Sunday (not available)
        $sunday = new \DateTime('2025-07-06');
        $this->assertFalse($this->availabilityChecker->isDeskAvailableOnDate($desk, $sunday));
    }

    public function testIsDeskAvailableWithNullAvailability(): void
    {
        $desk = $this->createTestDesk(true);
        $desk->getSpace()->setAvailability(null);
        $date = new \DateTime('2025-07-01');

        // Should handle null availability gracefully
        $result = $this->availabilityChecker->isDeskAvailableOnDate($desk, $date);

        $this->assertFalse($result);
    }
}
