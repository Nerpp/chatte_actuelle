<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email',TextType::class,[
            'attr' => [
            'placeholder' => 'email@exemple.com'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner votre adresse mail',
                ]),
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

        ->add('imgProfile', FileType::class, [
            'label' => 'Choisir un fichier',
            'mapped' => false,
            'multiple' => false,
            'required' => false,
            'constraints' => [
              new All([
                'constraints' => [
                  new File([
                    'maxSize' => '100k',
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

          ->add('plainPassword',RepeatedType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'type' => PasswordType::class,
            'mapped' => false,
            'empty_data' => "",
            'invalid_message' => 'La confirmation du mot de passe doit être identique au mot de passe.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            'first_options'  => [
                'label' => 'Password',
                'attr' => [
                    'placeholder' => 'Le mot de passe doit contenir 8 caractéres mininimum avec des caractéres spéciaux'
                ],
            ],
            'second_options' => [
                'label' => 'Repeat Password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Veuillez confirmer le mot de passe'

                ],
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrer un mot de passe s\'il vous plait',
                ]),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Votre mot de passe doit contenir {{ limit }} caractéres minimum',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
            
            // ->add('plainPassword', PasswordType::class, [
            //     // instead of being set onto the object directly,
            //     // this is read and encoded in the controller
            //     'mapped' => false,
            //     'placeholder' => 'email@exemple.com',
            //     'attr' => ['autocomplete' => 'new-password'],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Veuillez crée un mot de passe',
            //         ]),
            //         new Length([
            //             'min' => 8,
            //             'minMessage' => 'Your password should be at least {{ limit }} characters',
            //             // max length allowed by Symfony for security reasons
            //             'max' => 4096,
            //         ]),
            //     ],
            // ])

            ->add('captcha', TextType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ecrivez votre réponse ici'
                ]
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])

            ->add('save', SubmitType::class, ['label' => 'Sauvegarder'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
