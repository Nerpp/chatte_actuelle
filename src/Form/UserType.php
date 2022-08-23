<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           
            ->add('imgProfile', FileType::class, [
              'label' => 'Choisir un fichier',
  
              // unmapped means that this field is not associated to any entity property
              'mapped' => false,
  
              // make it optional so you don't have to re-upload the PDF file
              // every time you edit the Product details
              'required' => false,
  
              // unmapped fields can't define their validation using annotations
              // in the associated entity, so you can use the PHP constraint classes
              'constraints' => [
                  new File([
                      // 'maxSize' => '200k',
                      // 'maxSizeMessage' => 'Votre image de profil est trop importante ({{ size }} {{ suffix }}). Taille maximale autorisé {{ limit }} {{ suffix }}',
                      'mimeTypes' => [
                          "image/png",
                                          "image/jpeg",
                                          "image/jpg",
                                          "image/gif",
                      ],
                      'mimeTypesMessage' => 'Les formats autorisés sont de type jpeg, jpg, gif',
                  ])
              ],
          ])
              ->add('email',EmailType::class,[
                'attr' => [
                'placeholder' => 'email@exemple.com',
                'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre adresse mail',
                    ]),
                ]
            ])
            
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                  'Utilisateur' => 'ROLE_USER',
                  'Administrateur' => 'ROLE_ADMIN',
                  'Décisionnaire' => 'ROLE_SUPERADMIN'
                ]
            ])
           
            ->add('displayName',TextType::class,[
              'attr' => [
              'placeholder' => 'Votre pseudonyme'
              ],
              'constraints' => [
                  new NotBlank([
                      'message' => 'Veuillez renseigner un pseudonyme',
                  ]),
              ]
          ])

            // ->add('isVerified')
            ->add('warning')
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
