<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['data'];

        $builder
            ->add('firstname', TextType::class, [
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
            ]);

        if (in_array('ROLE_ADMIN', $options['current_user_roles'] ?? [])) {
            $builder
                ->add('email', EmailType::class, [
                    'required' => true,
                ])
                ->add('roles', ChoiceType::class, [
                    'data' => $user->getRoles()[0],
                    'choices' => [
                        'Guest' => 'ROLE_GUEST',
                        'Host' => 'ROLE_HOST',
                        'Admin' => 'ROLE_ADMIN',
                    ],
                    'mapped' => false
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current_user_roles' => [],
        ]);
    }
}