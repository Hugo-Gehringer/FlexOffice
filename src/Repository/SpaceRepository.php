<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Space>
 */
class SpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Space::class);
    }

    /**
     * @return Space[] Returns an array of Space objects hosted by a specific user
     */
    public function findByHost($host): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.host = :host')
            ->setParameter('host', $host)
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Space[] Returns an array of Space objects matching the search query
     */
    public function findBySearchQuery(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.host', 'h')
            ->leftJoin('s.address', 'a')
            ->andWhere('s.name LIKE :query OR s.description LIKE :query OR h.firstname LIKE :query OR h.lastname LIKE :query OR a.city LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Space
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
