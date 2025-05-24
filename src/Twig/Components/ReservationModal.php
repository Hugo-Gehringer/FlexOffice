<?php

namespace App\Twig\Components;

use App\Entity\Desk;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('reservation_modal')]
class ReservationModal extends AbstractController
{
    public $desk;
    public $bookedDates = [];

    public function __construct(
        private ReservationRepository $reservationRepository
    ) {
    }

    public function mount(Desk $desk): void
    {
        $this->desk = $desk;
        
        // Get existing reservations for this desk (excluding cancelled ones)
        $existingReservations = $this->reservationRepository->createQueryBuilder('r')
            ->select('r.reservationDate')
            ->where('r.desk = :desk')
            ->andWhere('r.status != :cancelledStatus') // Exclude cancelled reservations
            ->setParameter('desk', $desk)
            ->setParameter('cancelledStatus', 2) // 2 = cancelled
            ->getQuery()
            ->getResult();

        // Format the dates for JavaScript
        $bookedDates = [];
        foreach ($existingReservations as $existingReservation) {
            if ($existingReservation['reservationDate'] instanceof \DateTimeInterface) {
                $bookedDates[] = $existingReservation['reservationDate']->format('Y-m-d');
            }
        }
        $this->bookedDates = $bookedDates;
    }
}
