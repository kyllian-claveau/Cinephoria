<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\Film;
use App\Entity\Room;
use App\Entity\Session;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
            $builder
                ->add('startDate', DateTimeType::class, [
                    'widget' => 'single_text'
                ])
                ->add('endDate', DateTimeType::class, [
                    'widget' => 'single_text'
                ])
                ->add('film', EntityType::class, [
                    'class' => Film::class,
                    'choice_label' => 'title',
                    'placeholder' => 'Choisir un film',
                ])
                ->add('room', EntityType::class, [
                    'class' => Room::class,
                    'choice_label' => 'number',
                    'placeholder' => 'Choisir une salle',
                ])
                ->add('cinema', EntityType::class, [
                    'class' => Cinema::class,
                    'choice_label' => 'name',
                    'label' => 'Cinéma',
                    'placeholder' => 'Sélectionnez un cinéma',
                ])
                ->add('price', MoneyType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class
        ]);
    }
}

