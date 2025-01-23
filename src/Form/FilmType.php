<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\Genre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
            $builder
                ->add('title')
                ->add('filmFile',FileType::class,options: [
                    'required' => false
                ])
                ->add('description')
                ->add('cinemas', EntityType::class, [
                    'class' => Cinema::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('genres', EntityType::class, [
                    'class' => Genre::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('isFavorite')
                ->add('ageMin')
                ->add('duration');
    }
}

