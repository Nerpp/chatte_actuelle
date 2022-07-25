<?php

namespace App\Form;

use App\Entity\Articles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleAutocompleterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('title')
            ->add('title', EntityType::class, [
                'class' => Articles::class,
                'choice_label' => 'title',
                'placeholder' => 'Rechercher votre article',
               'autocomplete' => true,
            ])
            // ->add('slug')
            // ->add('article')
            // ->add('publishedAt')
            // ->add('modifiedAt')
            // ->add('draft')
            // ->add('censure')
            // ->add('user')
            // ->add('tags')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
