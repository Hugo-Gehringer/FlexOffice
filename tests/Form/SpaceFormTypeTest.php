<?php

namespace App\Tests\Form;

use App\Entity\Space;
use App\Form\SpaceFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class SpaceFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Espace de coworking moderne',
            'description' => 'Un espace de travail collaboratif avec tous les équipements nécessaires pour une productivité optimale',
        ];

        $space = new Space();
        $form = $this->factory->create(SpaceFormType::class, $space);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('Espace de coworking moderne', $space->getName());
        $this->assertEquals('Un espace de travail collaboratif avec tous les équipements nécessaires pour une productivité optimale', $space->getDescription());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(SpaceFormType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('description'));
        $this->assertTrue($form->has('address'));
        $this->assertTrue($form->has('availability'));
        $this->assertTrue($form->has('save'));
    }

    public function testEmptyFormData(): void
    {
        $formData = [
            'name' => '',
            'description' => '',
        ];

        $form = $this->factory->create(SpaceFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }

    public function testMinimumValidData(): void
    {
        $formData = [
            'name' => 'ABC',
            'description' => 'Description valide',
        ];

        $space = new Space();
        $form = $this->factory->create(SpaceFormType::class, $space);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('ABC', $space->getName());
        $this->assertEquals('Description valide', $space->getDescription());
    }

    public function testLongNameData(): void
    {
        $longName = str_repeat('a', 60); // Nom de 60 caractères

        $formData = [
            'name' => $longName,
            'description' => 'Description valide pour ce test',
        ];

        $space = new Space();
        $form = $this->factory->create(SpaceFormType::class, $space);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($longName, $space->getName());
    }

    public function testFormView(): void
    {
        $form = $this->factory->create(SpaceFormType::class);
        $view = $form->createView();

        $this->assertEquals('space_form', $view->vars['name']);
        $this->assertArrayHasKey('name', $view->children);
        $this->assertArrayHasKey('description', $view->children);
        $this->assertArrayHasKey('address', $view->children);
        $this->assertArrayHasKey('availability', $view->children);
        $this->assertArrayHasKey('save', $view->children);
    }

    public function testFormLabels(): void
    {
        $form = $this->factory->create(SpaceFormType::class);
        $view = $form->createView();

        $this->assertEquals('Nom de l\'espace', $view->children['name']->vars['label']);
        $this->assertEquals('Description', $view->children['description']->vars['label']);
        $this->assertEquals('Create Space', $view->children['save']->vars['label']);
    }

    public function testSpecialCharactersInName(): void
    {
        $formData = [
            'name' => 'Espace & Co - Café #1',
            'description' => 'Un espace unique avec des caractères spéciaux dans le nom',
        ];

        $space = new Space();
        $form = $this->factory->create(SpaceFormType::class, $space);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Espace & Co - Café #1', $space->getName());
    }

    public function testLongDescription(): void
    {
        $longDescription = 'Description très longue avec beaucoup de détails sur cet espace de coworking moderne qui offre tous les équipements nécessaires pour une productivité optimale dans un environnement collaboratif et convivial.';

        $formData = [
            'name' => 'Espace Test',
            'description' => $longDescription,
        ];

        $space = new Space();
        $form = $this->factory->create(SpaceFormType::class, $space);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($longDescription, $space->getDescription());
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(SpaceFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(Space::class, $config->getDataClass());
    }

    public function testFormFieldTypes(): void
    {
        $form = $this->factory->create(SpaceFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('name')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextareaType', get_class($form->get('description')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\SubmitType', get_class($form->get('save')->getConfig()->getType()->getInnerType()));
    }
}
