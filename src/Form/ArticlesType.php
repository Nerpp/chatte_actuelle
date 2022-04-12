<?php

namespace App\Form;

use App\Entity\Tags;
use App\Entity\Articles;

use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ArticlesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class,[
                'attr' => [
                    'placeholder' => 'Le titre de l\'article',
                   
                ]
            ])
            // ->add('slug')
            ->add('article', CKEditorType::class)
            // ->add('publishedAt')
            // ->add('modifiedAt')
            ->add('draft')
            // ->add('user')
            ->add('tags',EntityType::class,[
                'class' => Tags::class,
                'choice_label' => 'name',
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
