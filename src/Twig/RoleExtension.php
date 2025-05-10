<?php

namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoleExtension extends AbstractExtension
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_host', [$this, 'isHost']),
            new TwigFunction('is_guest', [$this, 'isGuest']),
            new TwigFunction('is_admin', [$this, 'isAdmin']),
        ];
    }

    public function isHost(): bool
    {
        return $this->security->isGranted('ROLE_HOST');
    }

    public function isGuest(): bool
    {
        return $this->security->isGranted('ROLE_GUEST');
    }

    public function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
