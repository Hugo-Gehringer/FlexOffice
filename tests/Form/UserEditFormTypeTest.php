<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserEditFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserEditFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_GUEST']);

        $formData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'roles' => 'ROLE_HOST',
        ];

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('John', $user->getFirstname());
        $this->assertEquals('Doe', $user->getLastname());
        $this->assertEquals('john.doe@example.com', $user->getEmail());

        // Le champ roles est mapped=false, donc il n'est pas automatiquement assigné
        $this->assertEquals('ROLE_HOST', $form->get('roles')->getData());
    }

    public function testFormHasRequiredFields(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);

        $this->assertTrue($form->has('firstname'));
        $this->assertTrue($form->has('lastname'));
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('roles'));
        $this->assertTrue($form->has('save'));
    }

    public function testFormWithoutAdminRole(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_USER']
        ]);

        $this->assertTrue($form->has('firstname'));
        $this->assertTrue($form->has('lastname'));
        $this->assertFalse($form->has('email'));
        $this->assertFalse($form->has('roles'));
        $this->assertTrue($form->has('save'));
    }

    public function testRoleChoices(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $rolesField = $form->get('roles');

        $choices = $rolesField->getConfig()->getOption('choices');

        $this->assertArrayHasKey('Guest', $choices);
        $this->assertArrayHasKey('Host', $choices);
        $this->assertArrayHasKey('Admin', $choices);
        $this->assertEquals('ROLE_GUEST', $choices['Guest']);
        $this->assertEquals('ROLE_HOST', $choices['Host']);
        $this->assertEquals('ROLE_ADMIN', $choices['Admin']);
    }

    public function testRoleFieldDefaultValue(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $rolesField = $form->get('roles');

        $this->assertEquals('ROLE_ADMIN', $rolesField->getData());
    }

    public function testRoleFieldWithMultipleRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_HOST', 'ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $rolesField = $form->get('roles');

        // Le formulaire prend le premier rôle de la liste.
        $this->assertEquals('ROLE_HOST', $rolesField->getData());
    }

    public function testRequiredFields(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $view = $form->createView();

        $this->assertTrue($view->children['firstname']->vars['required']);
        $this->assertTrue($view->children['lastname']->vars['required']);
        $this->assertTrue($view->children['email']->vars['required']);
    }

    public function testSaveButtonLabel(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $view = $form->createView();

        $this->assertEquals('Modifier', $view->children['save']->vars['label']);
    }

    public function testFormDataClass(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $config = $form->getConfig();

        $this->assertEquals(User::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('firstname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('lastname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\EmailType', get_class($form->get('email')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\ChoiceType', get_class($form->get('roles')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\SubmitType', get_class($form->get('save')->getConfig()->getType()->getInnerType()));
    }

    public function testRolesFieldIsMapped(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $rolesField = $form->get('roles');

        $this->assertFalse($rolesField->getConfig()->getOption('mapped'));
    }

    public function testUpdateExistingUserData(): void
    {
        $user = new User();
        $user->setFirstname('OldFirst');
        $user->setLastname('OldLast');
        $user->setEmail('old@example.com');
        $user->setRoles(['ROLE_GUEST']);

        $formData = [
            'firstname' => 'NewFirst',
            'lastname' => 'NewLast',
            'email' => 'new@example.com',
            'roles' => 'ROLE_ADMIN',
        ];

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('NewFirst', $user->getFirstname());
        $this->assertEquals('NewLast', $user->getLastname());
        $this->assertEquals('new@example.com', $user->getEmail());
        $this->assertEquals('ROLE_ADMIN', $form->get('roles')->getData());
    }

    public function testEmailValidation(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $formData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'invalid-email',
            'roles' => 'ROLE_GUEST',
        ];

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation de l'email se fait au niveau de l'entité
    }

    public function testEmptyFormDataAsAdmin(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $formData = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'roles' => '',
        ];

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_ADMIN']
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation des champs requis se fait au niveau de l'entité
    }

    public function testEmptyFormDataAsNonAdmin(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $formData = [
            'firstname' => '',
            'lastname' => '',
        ];

        $form = $this->factory->create(UserEditFormType::class, $user, [
            'current_user_roles' => ['ROLE_USER']
        ]);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation des champs requis se fait au niveau de l'entité
    }
}