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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            'label' => 'Brochure (PDF file)',

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
