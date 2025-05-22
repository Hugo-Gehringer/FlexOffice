<?php

namespace App\Repository;

use App\Entity\Availability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Availability>
 *
 * @method Availability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Availability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Availability[]    findAll()
 * @method Availability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }
    
    public function findOneBySpace(int $spaceId): ?Availability
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.space = :spaceId')
            ->setParameter('spaceId', $spaceId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findOneByDesk(int $deskId): ?Availability
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.desk = :deskId')
            ->setParameter('deskId', $deskId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
