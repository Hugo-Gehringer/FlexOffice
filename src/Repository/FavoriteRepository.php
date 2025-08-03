<?php

namespace App\Repository;

use App\Entity\Favorite;
use App\Entity\User;
use App\Entity\Space;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    /**
     * Check if a user has favorited a specific space
     */
    public function isFavorited(User $user, Space $space): bool
    {
        $favorite = $this->findOneBy([
            'user' => $user,
            'space' => $space
        ]);

        return $favorite !== null;
    }

    /**
     * Get a favorite record for a user and space
     */
    public function findFavorite(User $user, Space $space): ?Favorite
    {
        return $this->findOneBy([
            'user' => $user,
            'space' => $space
        ]);
    }

    /**
     * Get all favorites for a user with pagination support
     */
    public function findByUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.space', 's')
            ->leftJoin('s.address', 'a')
            ->leftJoin('s.host', 'h')
            ->addSelect('s', 'a', 'h')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC');
    }

    /**
     * Get count of favorites for a user
     */
    public function countByUser(User $user): int
    {
        return $this->count(['user' => $user]);
    }

    /**
     * Get count of times a space has been favorited
     */
    public function countBySpace(Space $space): int
    {
        return $this->count(['space' => $space]);
    }
}
