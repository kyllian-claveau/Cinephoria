<?php

namespace App\Form;

use App\Form\DataTransformer\JsonToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class RoomType extends AbstractType
{
    public function __construct(private readonly JsonToArrayTransformer $jsonToArrayTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('number')
            ->add('quality', ChoiceType::class, [
                'choices' => [
                    '3D' => '3D',
                    '4K' => '4K',
                    '4DX' => '4DX',
                ],
                'required' => true,
            ])
            ->add('accessibleSeats', HiddenType::class, [
                'required' => false,
            ])
            ->add('rowsRoom', IntegerType::class, [
                'label' => 'Nombre de rangées'
            ])
            ->add('columnsRoom', IntegerType::class, [
                'label' => 'Nombre de colonnes'
            ])
            ->add('stairs', HiddenType::class, [
                'required' => false,
            ]);

        $builder->get('stairs')->addModelTransformer($this->jsonToArrayTransformer);
        $builder->get('accessibleSeats')->addModelTransformer($this->jsonToArrayTransformer);
    }
}

