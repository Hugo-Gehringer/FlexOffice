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
            ])
            ->add('openingTime', TimeType::class, [
                'label' => 'Heure d\'ouverture',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => false,
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-gray-900 dark:text-white'],
                'attr' => ['class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'],
            ])
            ->add('closingTime', TimeType::class, [
                'label' => 'Heure de fermeture',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => false,
                'label_attr' => ['class' => 'block mb-2 text-sm font-medium text-gray-900 dark:text-white'],
                'attr' => ['class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Availability::class,
        ]);
    }
}
