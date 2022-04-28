<?php

namespace App\Form;

use App\Entity\Tags;
use App\Entity\Articles;

use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class ArticlesEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class,[
                'required'   => true,
                'attr' => [
                    'placeholder' => 'Le titre de l\'article',
                ]
            ])
            ->add('slug',HiddenType::class,[
                'required'   => false,
            ])
            ->add('article', CKEditorType::class,[
                'required' => true,
            ])

            ->add('draft',CheckboxType::class,[
                'required' => false
            ])

            ->add('tags',EntityType::class,[
                'class' => Tags::class,
                'choice_label' => 'name', 
                'placeholder' => '-- Sélectionnez le tag de l\'article * --',
                'required' => true,
            ])
            
            ->add('image', FileType::class, [
                'mapped' => false,
                'multiple' => true,
                'required' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
