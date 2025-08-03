<?php

namespace App\Tests\Form;

use App\Entity\Address;
use App\Form\AddressFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class AddressFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'street' => '123 Rue de la République',
            'city' => 'Montpellier',
            'postalCode' => '34000',
            'country' => 'France',
        ];

        $address = new Address();
        $form = $this->factory->create(AddressFormType::class, $address);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals('123 Rue de la République', $address->getStreet());
        $this->assertEquals('Montpellier', $address->getCity());
        $this->assertEquals('34000', $address->getPostalCode());
        $this->assertEquals('France', $address->getCountry());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(AddressFormType::class);

        $this->assertTrue($form->has('street'));
        $this->assertTrue($form->has('city'));
        $this->assertTrue($form->has('postalCode'));
        $this->assertTrue($form->has('country'));
    }

    public function testEmptyFormData(): void
    {
        $formData = [
            'street' => '',
            'city' => '',
            'postalCode' => '',
            'country' => '',
        ];

        $form = $this->factory->create(AddressFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // Note: La validation des contraintes se fait au niveau de l'entité
    }

    public function testCountryChoices(): void
    {
        $form = $this->factory->create(AddressFormType::class);
        $countryField = $form->get('country');

        $choices = $countryField->getConfig()->getOption('choices');

        $this->assertArrayHasKey('France', $choices);
        $this->assertEquals('France', $choices['France']);
    }

    public function testFormLabels(): void
    {
        $form = $this->factory->create(AddressFormType::class);
        $view = $form->createView();

        $this->assertEquals('Street Address', $view->children['street']->vars['label']);
        $this->assertEquals('City', $view->children['city']->vars['label']);
        $this->assertEquals('Postal Code', $view->children['postalCode']->vars['label']);
        $this->assertEquals('Pays', $view->children['country']->vars['label']);
    }

    public function testRequiredFields(): void
    {
        $form = $this->factory->create(AddressFormType::class);
        $view = $form->createView();

        $this->assertTrue($view->children['street']->vars['required']);
        $this->assertTrue($view->children['city']->vars['required']);
        $this->assertTrue($view->children['postalCode']->vars['required']);
        $this->assertTrue($view->children['country']->vars['required']);
    }

    public function testCountryPlaceholder(): void
    {
        $form = $this->factory->create(AddressFormType::class);
        $view = $form->createView();

        $this->assertEquals('Choisir un pays', $view->children['country']->vars['placeholder']);
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(AddressFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(Address::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->factory->create(AddressFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('street')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('city')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\TextType', get_class($form->get('postalCode')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\ChoiceType', get_class($form->get('country')->getConfig()->getType()->getInnerType()));
    }

    public function testSpecialCharactersInFields(): void
    {
        $formData = [
            'street' => 'Rue de l\'Église, 42',
            'city' => 'Saint-Étienne',
            'postalCode' => '42000',
            'country' => 'France',
        ];

        $address = new Address();
        $form = $this->factory->create(AddressFormType::class, $address);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Rue de l\'Église, 42', $address->getStreet());
        $this->assertEquals('Saint-Étienne', $address->getCity());
    }
}
