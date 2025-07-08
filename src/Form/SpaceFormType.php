<?php

namespace App\Form;

use App\Entity\Space;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpaceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'espace',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('address', AddressFormType::class)
            ->add('availability', AvailabilityFormType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Create Space',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Space::class,
        ]);
    }
}