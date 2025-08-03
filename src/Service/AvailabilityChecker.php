<?php

namespace App\Service;

use App\Entity\Availability;
use App\Entity\Desk;
use App\Repository\ReservationRepository;

class AvailabilityChecker
{
    public function __construct(
        private ReservationRepository $reservationRepository
    ) {
    }

    /**
     * Check if a desk is available on a specific date
     *
     * @param Desk $desk The desk to check
     * @param \DateTimeInterface $date The date to check
     * @return bool True if the desk is available, false otherwise
     */
    public function isDeskAvailableOnDate(Desk $desk, \DateTimeInterface $date): bool
    {
        // First check if the desk itself is marked as available
        if (!$desk->isAvailable()) {
            return false;
        }

        // Then check if the space has availability for this day of the week
        $space = $desk->getSpace();
        $availability = $space->getAvailability();

        // If no availability schedule is defined, consider the space unavailable
        if ($availability === null) {
            return false;
        }

        if (!$this->isDayAvailable($availability, $date)) {
            return false;
        }

        // Finally check if there are no existing reservations for this desk on this date
        return $this->reservationRepository->isDeskAvailableOnDate($desk, $date);
    }

    /**
     * Check if a day is available based on the availability schedule
     *
     * @param Availability $availability The availability schedule
     * @param \DateTimeInterface $date The date to check
     * @return bool True if the day is available, false otherwise
     */
    private function isDayAvailable(Availability $availability, \DateTimeInterface $date): bool
    {
        $dayOfWeek = strtolower($date->format('l')); // Get day name (monday, tuesday, etc.)

        // Check if the day is available in the availability schedule
        return match($dayOfWeek) {
            'monday' => $availability->isMonday(),
            'tuesday' => $availability->isTuesday(),
            'wednesday' => $availability->isWednesday(),
            'thursday' => $availability->isThursday(),
            'friday' => $availability->isFriday(),
            'saturday' => $availability->isSaturday(),
            'sunday' => $availability->isSunday(),
            default => false,
        };
    }
}
