<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class HomepageControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    public function testHomepageRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testHomepageDisplaysForRegularUser(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('title');
    }

    public function testHomepageDisplaysForHost(): void
    {
        $client = static::createClient();

        $host = UserFactory::createOne(['roles' => ['ROLE_HOST']]);
        $client->loginUser($host->_real());

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('title');
    }

    public function testHomepageRedirectsAdminToDashboard(): void
    {
        $client = static::createClient();

        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $client->loginUser($admin->_real());

        $client->request('GET', '/');

        $this->assertResponseRedirects('/admin/');
    }

    public function testHomepageShowsCorrectContentForGuest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testHomepageShowsCorrectContentForLoggedInUser(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $client->loginUser($user->_real());

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('title');
    }
}
