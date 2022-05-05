<?php

namespace App\Form;

use App\Entity\Edito;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EditoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('edito', TextareaType::class, [
                'mapped' => true,
                'required' => true,
                'attr' => [
                    'rows' => '20',
                    'cols' => '12',
                    'placeholder' => "L'édito de vos humeurs *",
                    'minlength' => 1,
                    'maxlength' => 65000,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Edito::class,
        ]);
    }
}
