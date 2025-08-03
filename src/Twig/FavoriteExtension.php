<?php

namespace App\Twig;

use App\Entity\Space;
use App\Entity\User;
use App\Service\FavoriteService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FavoriteExtension extends AbstractExtension
{
    public function __construct(
        private FavoriteService $favoriteService,
        private Security $security
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_favorited', [$this, 'isFavorited']),
            new TwigFunction('favorite_count', [$this, 'getFavoriteCount']),
        ];
    }

    public function isFavorited(Space $space): bool
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return false;
        }

        return $this->favoriteService->isFavorited($user, $space);
    }

    public function getFavoriteCount(Space $space): int
    {
        return $this->favoriteService->getFavoriteCount($space);
    }
}
