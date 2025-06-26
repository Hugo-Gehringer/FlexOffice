<?php

namespace App\Form;

use App\Entity\Availability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monday', CheckboxType::class, [
                'label' => 'Lundi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('tuesday', CheckboxType::class, [
                'label' => 'Mardi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('wednesday', CheckboxType::class, [
                'label' => 'Mercredi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('thursday', CheckboxType::class, [
                'label' => 'Jeudi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('friday', CheckboxType::class, [
                'label' => 'Vendredi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('saturday', CheckboxType::class, [
                'label' => 'Samedi',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ])
            ->add('sunday', CheckboxType::class, [
                'label' => 'Dimanche',
                'required' => false,
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['class' => 'hidden peer'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Availability::class,
        ]);
    }
}
