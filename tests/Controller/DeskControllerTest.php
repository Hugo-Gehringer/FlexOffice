<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Factory\SpaceFactory;
use App\Factory\DeskFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DeskControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    public function testNewDeskRequiresAuthentication(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->request('GET', '/desk/new/' . $space->getId());
        $this->assertResponseRedirects('/login');
    }

    public function testNewDeskRequiresHostRole(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_USER']]);
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->loginUser($user->_real());

        $client->request('GET', '/desk/new/' . $space->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNewDeskDeniedForNonOwner(): void
    {
        $client = static::createClient();

        $owner = UserFactory::createOne(['roles' => ['ROLE_HOST']]);
        $space = SpaceFactory::createOne(['host' => $owner]);

        $otherUser = UserFactory::createOne([
            'roles' => ['ROLE_HOST'],
            'email' => 'other@example.com'
        ]);

        $client->loginUser($otherUser->_real());

        $client->request('GET', '/desk/new/' . $space->getId());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNewDeskFormIsDisplayedForSpaceOwner(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_HOST']]);
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->loginUser($user->_real());

        $client->request('GET', '/desk/new/' . $space->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="desk_form"]');
    }

    public function testCreateNewDesk(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne(['roles' => ['ROLE_HOST']]);
        $space = SpaceFactory::createOne(['host' => $user]);

        $client->loginUser($user->_real());
        // Correction de l'URL de la requête GET
        $crawler = $client->request('GET', '/desk/new/' . $space->getId());

        // Le champ 'isAvailable' a été retiré car il n'est pas dans le formulaire
        $form = $crawler->selectButton('Créer Bureau')->form([
            'desk_form[name]' => 'Test Desk',
            'desk_form[description]' => 'Test Description',
            'desk_form[type]' => 0,
            'desk_form[pricePerDay]' => 25,
            'desk_form[capacity]' => 1,
            'desk_form[equipments]' => [],
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/space/' . $space->getId(), $client->getResponse()->headers->get('Location'));
    }

}