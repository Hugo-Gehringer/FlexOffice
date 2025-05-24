<?php

namespace App\Repository;

use App\Entity\Desk;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Find reservations for a desk on a specific date
     *
     * @param Desk $desk The desk to check
     * @param \DateTimeInterface $date The date to check
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findReservationsForDeskOnDate(Desk $desk, \DateTimeInterface $date): array
    {
        // Create start and end of the day
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0);

        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->andWhere('r.desk = :desk')
            ->andWhere('r.reservationDate >= :startDate')
            ->andWhere('r.reservationDate <= :endDate')
            ->andWhere('r.status != :cancelledStatus') // Exclude cancelled reservations
            ->setParameter('desk', $desk)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cancelledStatus', 2) // 2 = cancelled
            ->getQuery()
            ->getResult();
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
        $reservations = $this->findReservationsForDeskOnDate($desk, $date);
        return count($reservations) === 0;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
