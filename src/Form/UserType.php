<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles', ChoiceType::class, [
            //     'required' => true,
            //     'multiple' => false,
            //     'expanded' => false,
            //     'choices'  => [
            //       'ROLE_USER' => 'ROLE_USER',
            //       'ROLE_ADMIN' => 'ROLE_ADMIN'
            //     ]

            // ])
            ->add('roles', ChoiceType::class, [
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'Utilisateur' => null,
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPERADMIN'
                ],
            ])
            ->add('password')
            ->add('displayname')
            // ->add('isVerified')
            // ->add('warning')
            // ->add('blackList')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
