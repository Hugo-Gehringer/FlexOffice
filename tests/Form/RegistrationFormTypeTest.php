<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class RegistrationFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'newuser@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'userRole' => 'ROLE_GUEST',
            'plainPassword' => 'password123',
        ];

        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('newuser@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstname());
        $this->assertEquals('Doe', $user->getLastname());
        // Les champs non "mapped" ne sont pas hydratés sur l'entité, à tester côté handler ou event
        $this->assertEquals('ROLE_GUEST', $form->get('userRole')->getData());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('firstname'));
        $this->assertTrue($form->has('lastname'));
        $this->assertTrue($form->has('userRole'));
        $this->assertTrue($form->has('plainPassword'));
    }

    public function testUserRoleChoices(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $userRoleField = $form->get('userRole');

        $choices = $userRoleField->getConfig()->getOption('choices');

        $this->assertArrayHasKey(' Guest', $choices);
        $this->assertArrayHasKey('  Host', $choices);
        $this->assertEquals('ROLE_GUEST', $choices[' Guest']);
        $this->assertEquals('ROLE_HOST', $choices['  Host']);
    }

    public function testUserRoleDefaultValue(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $userRoleField = $form->get('userRole');

        $this->assertEquals('ROLE_GUEST', $userRoleField->getConfig()->getOption('data'));
    }

    public function testFormLabels(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $view = $form->createView();

        $this->assertEquals('First Name', $view->children['firstname']->vars['label']);
        $this->assertEquals('Last Name', $view->children['lastname']->vars['label']);
        $this->assertEquals('I want to', $view->children['userRole']->vars['label']);
    }

    public function testRequiredFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $view = $form->createView();

        $this->assertTrue($view->children['firstname']->vars['required']);
        $this->assertTrue($view->children['lastname']->vars['required']);
        $this->assertTrue($view->children['userRole']->vars['required']);
    }

    public function testPasswordFieldAttributes(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $passwordField = $form->get('plainPassword');

        $attributes = $passwordField->getConfig()->getOption('attr');
        $this->assertEquals('new-password', $attributes['autocomplete']);
    }

    public function testUserRoleFieldConfiguration(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $userRoleField = $form->get('userRole');
        $config = $userRoleField->getConfig();

        $this->assertFalse($config->getOption('mapped'));
        $this->assertTrue($config->getOption('expanded'));
        $this->assertFalse($config->getOption('multiple'));
    }

    public function testUnmappedFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);

        $this->assertFalse($form->get('userRole')->getConfig()->getOption('mapped'));
        $this->assertFalse($form->get('plainPassword')->getConfig()->getOption('mapped'));
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(User::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('email')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('firstname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('lastname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\ChoiceType', get_class($form->get('userRole')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\PasswordType', get_class($form->get('plainPassword')->getConfig()->getType()->getInnerType()));
    }

    public function testHostUserRole(): void
    {
        $formData = [
            'email' => 'host@example.com',
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'userRole' => 'ROLE_HOST',
            'agreeTerms' => true,
            'plainPassword' => 'hostpassword123',
        ];

        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('ROLE_HOST', $form->get('userRole')->getData());
    }
}
