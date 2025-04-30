<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationDate', DateTimeType::class, [
                'label' => 'Reservation Date',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a reservation date',
                    ]),
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'The reservation date must be in the future',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
