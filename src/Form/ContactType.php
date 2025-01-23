<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => false, // Nom d'utilisateur est facultatif
                'label' => 'Nom d\'utilisateur (facultatif)'
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre de la demande'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la demande'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
