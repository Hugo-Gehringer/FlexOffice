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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                'label' => 'Nom du bureau',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de bureau',
                'required' => true,
                'choices' => array_flip(Desk::DESK_TYPES),
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('pricePerDay', IntegerType::class, [
                'label' => 'Prix par jour (€)',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité',
                'required' => true,
            ])
            ->add('equipments', EntityType::class, [
                'label' => 'Equipement',
                'class' => Equipment::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer Bureau',
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Desk::class,
        ]);
    }
}
