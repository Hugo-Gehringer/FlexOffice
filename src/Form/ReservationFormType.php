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
                'label' => 'Reservation Date',
                'widget' => 'single_text',        // Important : un seul input <input type="text">
                'format' => 'yyyy-MM-dd',         // Doit correspondre au format Flatpickr (Y-m-d)
                'input' => 'datetime',            // Retourne un objet \DateTime en PHP
                'required' => true,
                'attr' => [
                    'class' => 'datepicker',      // Permet de cibler le champ avec Flatpickr JS
                    'autocomplete' => 'off',      // (Optionnel) désactive l’autocomplétion navigateur
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Reserve',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);

//        $resolver->setAllowedTypes('desk', ['null', Desk::class]);
    }
}
