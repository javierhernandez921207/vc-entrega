<?php

namespace App\Form;

use App\Entity\Negocio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NegocioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('icono', ChoiceType::class, [
                'choices' => [
                    'Archive' => 'archive',
                    'Beer' => 'beer',
                    'Book' => 'book',
                    'Box' => 'box',
                    'Car' => 'car-side',
                    'Chair' => 'chair',
                    'Coffee' => 'coffee',
                    'Desktop' => 'desktop',
                    'Gamepad' => 'gamepad',
                    'Gem' => 'gem',
                    'Gift' => 'gift',
                    'Glass-martini' => 'glass-martini',
                    'Hammer' => 'hammer',
                    'Hamburger' => 'hamburger',
                    'Home' => 'home',
                    'Laptop' => 'laptop',
                    'Paint-roller' => 'paint-roller',
                    'Phone' => 'phone',
                    'Shoe' => 'shoe-prints',
                    'Tshirt' => 'tshirt',
                    'Tv' => 'tv'

                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Negocio::class,
        ]);
    }
}
