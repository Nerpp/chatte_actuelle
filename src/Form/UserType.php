<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imgProfile', FileType::class, [
                'label' => 'Choisir un fichier',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'constraints' => [
                  new All([
                    'constraints' => [
                      new File([
                        'maxSize' => '1024k',
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier conforme',
                        'mimeTypes' => [
                                            "image/png",
                                            "image/jpeg",
                                            "image/jpg",
                                            "image/gif",
                                        ],
                      ]),
                    ],
                  ]),
                ]
              ])
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
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPERADMIN'
                ],
            ])
            // ->add('password')
            ->add('displayname')
            // ->add('isVerified')
            // ->add('warning')
            // ->add('blackList')
        ;

          // Data transformer
          $builder->get('roles')
          ->addModelTransformer(new CallbackTransformer(
              function ($rolesArray) {
                   // transform the array to a string
                   return count($rolesArray) ? $rolesArray[0] : null;
              },
              function ($rolesString) {
                   // transform the string back to an array
                   return [$rolesString];
              }
          ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
