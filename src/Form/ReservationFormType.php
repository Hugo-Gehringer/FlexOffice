<?php

namespace App\Form;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Service\AvailabilityChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationFormType extends AbstractType
{
    private AvailabilityChecker $availabilityChecker;

    public function __construct(AvailabilityChecker $availabilityChecker)
    {
        $this->availabilityChecker = $availabilityChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('reservationDate', DateType::class, [
                'label' => 'Date de réservation',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input' => 'datetime',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Réserver',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
