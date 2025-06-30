<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Street Address',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a street address',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a city',
                    ]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a postal code',
                    ]),
                    new Length([
                        'max' => 5,
                        'maxMessage' => 'The postal code cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Country',
                'required' => true,
                'placeholder' => 'Select a country',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
