<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $options['categories'];

        $article = $options['article'];

         $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'data' => $article?->getTitle()
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['rows' => 8],
                'data' => $article ? html_entity_decode($article->getContent()) : null
            ])
            ->add('category', ChoiceType::class, [
                'placeholder' => 'Chose a category',
                'choices' => $categories,
                'data' => $article?->getCategory()->getId()
            ])
            ->add('addEdit', SubmitType::class, [
                'label' => $article ? 'Edit' : 'Create',
                'attr' => [
                    "class" => "btn btn-primary"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
         $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
            'categories' => [],
            'article' => null
        ]);
    }
}       