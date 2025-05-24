<?php

namespace App\Form;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Service\AvailabilityChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
        /** @var Desk|null $desk */
        $desk = $options['desk'] ?? null;

        // Get availability data for the desk's space
        $availabilityAttrs = [];
        if ($desk) {
            $availability = $desk->getSpace()->getAvailability();
            if ($availability) {
                $availabilityAttrs = [
                    'data-monday' => $availability->isMonday() ? '1' : '0',
                    'data-tuesday' => $availability->isTuesday() ? '1' : '0',
                    'data-wednesday' => $availability->isWednesday() ? '1' : '0',
                    'data-thursday' => $availability->isThursday() ? '1' : '0',
                    'data-friday' => $availability->isFriday() ? '1' : '0',
                    'data-saturday' => $availability->isSaturday() ? '1' : '0',
                    'data-sunday' => $availability->isSunday() ? '1' : '0',
                    'data-desk-id' => $desk->getId(),
                    'id' => 'reservation_form_reservationDate',
                ];
            }
        }

        $builder
            ->add('reservationDate', DateType::class, [
                'label' => 'Reservation Date',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'input' => 'datetime',
                'required' => true,
                'attr' => array_merge($availabilityAttrs, [
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-3 pr-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-pointer appearance-none',
                    'placeholder' => 'Select a date',
                    'autocomplete' => 'off',
                    'readonly' => true,
                ]),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a reservation date',
                    ]),
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'The reservation date must be in the future',
                    ]),
                    new Callback([
                        'callback' => function ($date, ExecutionContextInterface $context) use ($desk) {
                            if (!$date instanceof \DateTimeInterface || !$desk instanceof Desk) {
                                return;
                            }

                            if (!$this->availabilityChecker->isDeskAvailableOnDate($desk, $date)) {
                                $context->buildViolation('This desk is not available on the selected date.')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'desk' => null,
        ]);

        $resolver->setAllowedTypes('desk', ['null', Desk::class]);
    }
}
