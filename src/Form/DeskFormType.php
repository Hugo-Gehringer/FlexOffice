<?php

namespace App\Form;

use App\Entity\Availability;
use App\Entity\Desk;
use App\Entity\Equipment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class DeskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Desk Name',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name for the desk',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 60,
                        'minMessage' => 'The name should be at least {{ limit }} characters',
                        'maxMessage' => 'The name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Desk Type',
                'required' => true,
                'choices' => [
                    'Standard Desk' => 0,
                    'Standing Desk' => 1,
                    'Private Office' => 2,
                    'Meeting Room' => 3,
                    'Conference Room' => 4,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'minMessage' => 'The description should be at least {{ limit }} character',
                    ]),
                ],
            ])
            ->add('pricePerDay', IntegerType::class, [
                'label' => 'Price per Day (€)',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a price for the desk',
                    ]),
                    new Positive([
                        'message' => 'The price must be positive',
                    ]),
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacity',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the capacity of the desk',
                    ]),
                    new Positive([
                        'message' => 'The capacity must be positive',
                    ]),
                ],
            ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Available for booking',
                'required' => false,
                'data' => true, // Default to available
            ])
            ->add('equipments', EntityType::class, [
                'label' => 'Equipment',
                'class' => Equipment::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
            // Removed availability field as desks now use their parent space's availability
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Desk::class,
        ]);
    }
}
