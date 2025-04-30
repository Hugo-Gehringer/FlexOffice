<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Space;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SpaceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Space Name',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name for your space',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 60,
                        'minMessage' => 'The name should be at least {{ limit }} characters',
                        'maxMessage' => 'The name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a description for your space',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'The description should be at least {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('plan', TextType::class, [
                'label' => 'Floor Plan (optional)',
                'required' => false,
            ])
            ->add('address', AddressFormType::class, [
                'label' => false,
                'required' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Space::class,
        ]);
    }
}
