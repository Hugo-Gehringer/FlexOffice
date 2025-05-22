<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_admin_dashboard'));
        }
        if (in_array('ROLE_HOST', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_my_spaces'));
        }
        if (in_array('ROLE_GUEST', $user->getRoles(), true)) {
            return new RedirectResponse($this->router->generate('app_reservation_index'));
        }
        // Ajoutez d'autres rôles si besoin
        return new RedirectResponse($this->router->generate('app_homepage'));
    }
}
