<?php

namespace App\Tests\Form;

use App\Entity\Availability;
use App\Form\AvailabilityFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class AvailabilityFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'sunday' => false,
        ];

        $availability = new Availability();
        $form = $this->factory->create(AvailabilityFormType::class, $availability);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertTrue($availability->isMonday());
        $this->assertTrue($availability->isTuesday());
        $this->assertTrue($availability->isWednesday());
        $this->assertTrue($availability->isThursday());
        $this->assertTrue($availability->isFriday());
        $this->assertFalse($availability->isSaturday());
        $this->assertFalse($availability->isSunday());
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);

        $this->assertTrue($form->has('monday'));
        $this->assertTrue($form->has('tuesday'));
        $this->assertTrue($form->has('wednesday'));
        $this->assertTrue($form->has('thursday'));
        $this->assertTrue($form->has('friday'));
        $this->assertTrue($form->has('saturday'));
        $this->assertTrue($form->has('sunday'));
    }

    public function testAllFieldsAreOptional(): void
    {
        $formData = [
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
            'saturday' => false,
            'sunday' => false,
        ];

        $form = $this->factory->create(AvailabilityFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }

    public function testFormLabels(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);
        $view = $form->createView();

        $this->assertEquals('Lundi', $view->children['monday']->vars['label']);
        $this->assertEquals('Mardi', $view->children['tuesday']->vars['label']);
        $this->assertEquals('Mercredi', $view->children['wednesday']->vars['label']);
        $this->assertEquals('Jeudi', $view->children['thursday']->vars['label']);
        $this->assertEquals('Vendredi', $view->children['friday']->vars['label']);
        $this->assertEquals('Samedi', $view->children['saturday']->vars['label']);
        $this->assertEquals('Dimanche', $view->children['sunday']->vars['label']);
    }

    public function testFieldsAreNotRequired(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);
        $view = $form->createView();

        $this->assertFalse($view->children['monday']->vars['required']);
        $this->assertFalse($view->children['tuesday']->vars['required']);
        $this->assertFalse($view->children['wednesday']->vars['required']);
        $this->assertFalse($view->children['thursday']->vars['required']);
        $this->assertFalse($view->children['friday']->vars['required']);
        $this->assertFalse($view->children['saturday']->vars['required']);
        $this->assertFalse($view->children['sunday']->vars['required']);
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(Availability::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('monday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('tuesday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('wednesday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('thursday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('friday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('saturday')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\CheckboxType', get_class($form->get('sunday')->getConfig()->getType()->getInnerType()));
    }

    public function testWeekendOnly(): void
    {
        $formData = [
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
            'saturday' => true,
            'sunday' => true,
        ];

        $availability = new Availability();
        $form = $this->factory->create(AvailabilityFormType::class, $availability);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($availability->isMonday());
        $this->assertFalse($availability->isFriday());
        $this->assertTrue($availability->isSaturday());
        $this->assertTrue($availability->isSunday());
    }

    public function testEmptySubmission(): void
    {
        $availability = new Availability();
        $form = $this->factory->create(AvailabilityFormType::class, $availability);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
        // Par défaut, tous les jours devraient être false
        $this->assertFalse($availability->isMonday());
        $this->assertFalse($availability->isTuesday());
        $this->assertFalse($availability->isWednesday());
        $this->assertFalse($availability->isThursday());
        $this->assertFalse($availability->isFriday());
        $this->assertFalse($availability->isSaturday());
        $this->assertFalse($availability->isSunday());
    }

    public function testCssClasses(): void
    {
        $form = $this->factory->create(AvailabilityFormType::class);
        $view = $form->createView();

        // Vérifier les classes CSS personnalisées
        $this->assertStringContainsString('sr-only', $view->children['monday']->vars['label_attr']['class']);
        $this->assertStringContainsString('hidden peer', $view->children['monday']->vars['attr']['class']);
    }
}
