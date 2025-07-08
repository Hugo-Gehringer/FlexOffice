<?php

namespace App\Tests\Form;

use App\Entity\Desk;
use App\Entity\Equipment;
use App\Form\DeskFormType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class DeskFormTypeTest extends \App\Tests\DatabaseTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = static::getContainer()->get('form.factory');
    }


    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Bureau Principal',
            'type' => Desk::DESK_TYPE_STANDARD,
            'description' => 'Un bureau moderne avec vue sur la ville, parfait pour la concentration.',
            'pricePerDay' => 35,
            'capacity' => 1,
            'equipments' => [],
        ];

        $desk = new Desk();
        $form = $this->formFactory->create(DeskFormType::class, $desk);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('Bureau Principal', $desk->getName());
        $this->assertEquals(Desk::DESK_TYPE_STANDARD, $desk->getType());
        $this->assertEquals('Un bureau moderne avec vue sur la ville, parfait pour la concentration.', $desk->getDescription());
        $this->assertEquals(35, $desk->getPricePerDay());
        $this->assertEquals(1, $desk->getCapacity());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('type'));
        $this->assertTrue($form->has('description'));
        $this->assertTrue($form->has('pricePerDay'));
        $this->assertTrue($form->has('capacity'));
        $this->assertTrue($form->has('equipments'));
    }

    public function testDeskTypeChoices(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $typeField = $form->get('type');

        $choices = $typeField->getConfig()->getOption('choices');

        $expectedChoices = array_flip(Desk::DESK_TYPES);
        $this->assertEquals($expectedChoices, $choices);
    }

    public function testFormLabels(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $view = $form->createView();

        $this->assertEquals('Nom du bureau', $view->children['name']->vars['label']);
        $this->assertEquals('Type de bureau', $view->children['type']->vars['label']);
        $this->assertEquals('Description', $view->children['description']->vars['label']);
        $this->assertEquals('Prix par jour (€)', $view->children['pricePerDay']->vars['label']);
        $this->assertEquals('Capacité', $view->children['capacity']->vars['label']);
        $this->assertEquals('Equipement', $view->children['equipments']->vars['label']);
    }

    public function testRequiredFields(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $view = $form->createView();

        $this->assertTrue($view->children['name']->vars['required']);
        $this->assertTrue($view->children['type']->vars['required']);
        $this->assertTrue($view->children['description']->vars['required']);
        $this->assertTrue($view->children['pricePerDay']->vars['required']);
        $this->assertTrue($view->children['capacity']->vars['required']);
        $this->assertFalse($view->children['equipments']->vars['required']);
    }

    public function testPricePerDayAttributes(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $priceField = $form->get('pricePerDay');

        $attributes = $priceField->getConfig()->getOption('attr');
        $this->assertEquals(1, $attributes['min']);
        $this->assertEquals(1, $attributes['step']);
    }

    public function testFormDataClass(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(Desk::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('name')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\ChoiceType', get_class($form->get('type')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextareaType', get_class($form->get('description')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\IntegerType', get_class($form->get('pricePerDay')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Bridge\Doctrine\Form\Type\EntityType', get_class($form->get('equipments')->getConfig()->getType()->getInnerType()));
    }

    public function testEquipmentFieldConfiguration(): void
    {
        $form = $this->formFactory->create(DeskFormType::class);
        $equipmentField = $form->get('equipments');
        $config = $equipmentField->getConfig();
        $this->assertEquals('name', $config->getOption('choice_label'));
        $this->assertTrue($config->getOption('multiple'));
        $this->assertFalse($config->getOption('expanded'));
        $this->assertFalse($config->getOption('required'));
    }

    public function testDifferentDeskTypes(): void
    {
        $formData = [
            'name' => 'Salle de Conférence A',
            'type' => Desk::DESK_TYPE_CONFERENCE_ROOM,
            'description' => 'Grande salle de conférence pour 20 personnes.',
            'pricePerDay' => 150,
            'capacity' => 20,
            'equipments' => [],
        ];

        $desk = new Desk();
        $form = $this->formFactory->create(DeskFormType::class, $desk);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(Desk::DESK_TYPE_CONFERENCE_ROOM, $desk->getType());
        $this->assertEquals(20, $desk->getCapacity());
        $this->assertEquals(150, $desk->getPricePerDay());
    }
}