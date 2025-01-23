<?php

namespace App\Form;

use App\Entity\Film;
use App\Entity\Cinema;
use App\Entity\Genre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilmFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cinema', ChoiceType::class, [
                'choices' => $options['cinemas'],
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sélectionner un cinéma',
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => $options['genres'],
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sélectionner un genre',
            ])
            ->add('day', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Jour préféré',
                'attr' => ['class' => 'datepicker'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
            'cinemas' => [],
            'genres' => [],
        ]);
    }
}
