<?php

namespace App\Service;

use App\Entity\Space;
use App\Entity\User;
use App\Repository\FavoriteRepository;

class FavoriteService
{
    public function __construct(
        private FavoriteRepository $favoriteRepository
    ) {
    }

    /**
     * Check if a user has favorited a specific space
     */
    public function isFavorited(?User $user, Space $space): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favoriteRepository->isFavorited($user, $space);
    }

    /**
     * Get the count of favorites for a space
     */
    public function getFavoriteCount(Space $space): int
    {
        return $this->favoriteRepository->countBySpace($space);
    }

    /**
     * Get the count of favorites for a user
     */
    public function getUserFavoriteCount(User $user): int
    {
        return $this->favoriteRepository->countByUser($user);
    }
}
