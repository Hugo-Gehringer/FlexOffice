<?php

namespace App\Tests\Form;

use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Service\AvailabilityChecker;
use Symfony\Component\Form\Test\TypeTestCase;

class ReservationFormTypeTest extends TypeTestCase
{
    private $availabilityChecker;

    protected function setUp(): void
    {
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        parent::setUp();
    }

    protected function getExtensions()
    {
        // Mock du service AvailabilityChecker pour les tests
        $type = new ReservationFormType($this->availabilityChecker);

        return [
            new \Symfony\Component\Form\Extension\Core\CoreExtension(),
        ];
    }

    protected function getTypes()
    {
        return [
            new ReservationFormType($this->availabilityChecker),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'reservationDate' => '2025-08-15',
        ];

        $reservation = new Reservation();
        $form = $this->factory->create(ReservationFormType::class, $reservation);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(\DateTimeInterface::class, $reservation->getReservationDate());
        $this->assertEquals('2025-08-15', $reservation->getReservationDate()->format('Y-m-d'));
    }

    public function testFormHasRequiredFields(): void
    {
        $form = $this->factory->create(ReservationFormType::class);

        $this->assertTrue($form->has('reservationDate'));
        $this->assertTrue($form->has('save'));
    }

    public function testFormLabels(): void
    {
        $form = $this->factory->create(ReservationFormType::class);
        $view = $form->createView();

        $this->assertEquals('Date de réservation', $view->children['reservationDate']->vars['label']);
        $this->assertEquals('Réserver', $view->children['save']->vars['label']);
    }

    public function testDateFieldIsRequired(): void
    {
        $form = $this->factory->create(ReservationFormType::class);
        $view = $form->createView();

        $this->assertTrue($view->children['reservationDate']->vars['required']);
    }

    public function testDateFieldAttributes(): void
    {
        $form = $this->factory->create(ReservationFormType::class);
        $dateField = $form->get('reservationDate');

        $attributes = $dateField->getConfig()->getOption('attr');
        $this->assertEquals('datepicker', $attributes['class']);
        $this->assertEquals('off', $attributes['autocomplete']);
    }

    public function testDateFieldConfiguration(): void
    {
        $form = $this->factory->create(ReservationFormType::class);
        $dateField = $form->get('reservationDate');
        $config = $dateField->getConfig();

        $this->assertEquals('single_text', $config->getOption('widget'));
        $this->assertEquals('yyyy-MM-dd', $config->getOption('format'));
        $this->assertEquals('datetime', $config->getOption('input'));
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(ReservationFormType::class);
        $config = $form->getConfig();

        $this->assertEquals(Reservation::class, $config->getDataClass());
    }

    public function testFieldTypes(): void
    {
        $form = $this->factory->create(ReservationFormType::class);

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\DateType', get_class($form->get('reservationDate')->getConfig()->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\SubmitType', get_class($form->get('save')->getConfig()->getType()->getInnerType()));
    }

    public function testEmptyFormData(): void
    {
        $formData = [
            'reservationDate' => '',
        ];

        $form = $this->factory->create(ReservationFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation se fait au niveau de l'entité
    }

    public function testFutureDate(): void
    {
        $futureDate = new \DateTime('+1 month');
        $formData = [
            'reservationDate' => $futureDate->format('Y-m-d'),
        ];

        $reservation = new Reservation();
        $form = $this->factory->create(ReservationFormType::class, $reservation);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($futureDate->format('Y-m-d'), $reservation->getReservationDate()->format('Y-m-d'));
    }

    public function testPastDate(): void
    {
        $pastDate = new \DateTime('-1 day');
        $formData = [
            'reservationDate' => $pastDate->format('Y-m-d'),
        ];

        $reservation = new Reservation();
        $form = $this->factory->create(ReservationFormType::class, $reservation);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        // La validation des dates passées se fait au niveau de l'entité ou des contraintes
    }
}
