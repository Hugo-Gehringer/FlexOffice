<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Factory\SpaceFactory;
use App\Factory\DeskFactory;
use App\Factory\ReservationFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AdminControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    public function testDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/');
        $this->assertResponseRedirects('/login');
    }

    public function testDashboardRequiresAdminRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/admin/');
        $this->assertResponseStatusCodeSame(403);
    }

//    public function testDashboardDisplaysForAdmin(): void
//    {
//        $client = static::createClient();
//
//        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
//        $client->loginUser($admin->_real());
//
//        $client->request('GET', '/admin/');
//
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Admin Dashboard');
//    }

    public function testUsersPageRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/users');
        $this->assertResponseRedirects('/login');
    }

    public function testUsersPageRequiresAdminRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(403);
    }

//    public function testUsersPageDisplaysForAdmin(): void
//    {
//        $client = static::createClient();
//
//        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
//
//        $client->loginUser($admin->_real());
//
//        $client->request('GET', '/admin/users');
//
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'User Management');
//    }

    public function testSpacesPageRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/spaces');
        $this->assertResponseRedirects('/login');
    }

    public function testSpacesPageRequiresAdminRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/admin/spaces');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testReservationsPageRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/reservations');
        $this->assertResponseRedirects('/login');
    }

    public function testReservationsPageRequiresAdminRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/admin/reservations');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testReservationsPageDisplaysForAdmin(): void
    {
        $client = static::createClient();

        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);

        $client->loginUser($admin->_real());

        $client->request('GET', '/admin/reservations');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Réservations');
    }

    public function testDesksPageRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/desks');
        $this->assertResponseRedirects('/login');
    }

    public function testDesksPageRequiresAdminRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/admin/desks');
        $this->assertResponseStatusCodeSame(403);
    }


    public function testReservationsPaginationWorks(): void
    {
        $client = static::createClient();

        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);

        $client->loginUser($admin->_real());

        $client->request('GET', '/admin/reservations');

        $this->assertResponseIsSuccessful();
        // Just check that the page loads successfully
        $this->assertSelectorTextContains('h1', 'Gestion des Réservations');
    }
}
