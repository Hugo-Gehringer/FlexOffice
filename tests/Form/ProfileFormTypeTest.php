<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class ProfileFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $user = new User();
        $form = $this->factory->create(ProfileFormType::class, $user);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('John', $user->getFirstname());
        $this->assertEquals('Doe', $user->getLastName());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(ProfileFormType::class);

        $this->assertTrue($form->has('firstname'));
        $this->assertTrue($form->has('lastname'));
        $this->assertTrue($form->has('save'));
    }

    public function testRequiredFields(): void
    {
        $form = $this->factory->create(ProfileFormType::class);
        $view = $form->createView();

        $this->assertTrue($view->children['firstname']->vars['required']);
        $this->assertTrue($view->children['lastname']->vars['required']);
    }

    public function testSaveButtonLabel(): void
    {
        $form = $this->factory->create(ProfileFormType::class);
        $view = $form->createView();

        $this->assertEquals('Update Profile', $view->children['save']->vars['label']);
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(ProfileFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(User::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->factory->create(ProfileFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('firstname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('lastname')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\SubmitType', get_class($form->get('save')->getConfig()->getType()->getInnerType()));
    }

    public function testEmptyFormData(): void
    {
        $formData = [
            'firstname' => '',
            'lastname' => '',
        ];

        $form = $this->factory->create(ProfileFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation se fait au niveau de l'entité
    }

    public function testSpecialCharactersInNames(): void
    {
        $formData = [
            'firstname' => 'Jean-Marie',
            'lastname' => "O'Connor",
        ];

        $user = new User();
        $form = $this->factory->create(ProfileFormType::class, $user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Jean-Marie', $user->getFirstName());
        $this->assertEquals("O'Connor", $user->getLastName());
    }

    public function testUpdateExistingUser(): void
    {
        $user = new User();
        $user->setFirstName('OldFirst');
        $user->setLastName('OldLast');

        $formData = [
            'firstname' => 'NewFirst',
            'lastname' => 'NewLast',
        ];

        $form = $this->factory->create(ProfileFormType::class, $user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('NewFirst', $user->getFirstName());
        $this->assertEquals('NewLast', $user->getLastName());
    }
}
