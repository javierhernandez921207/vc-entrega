<?php

namespace App\Form;

use App\Controller\NegocioController;
use App\Entity\Cuadre;
use App\Entity\Negocio;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CuadreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fondo', MoneyType::class, [
                'label' => "Cantidad a dejar en caja",
                'currency' => 'USD',    // default value is 'EUR'
            ])
            ->add('trabajador_saliente', EntityType::class, [
                'class' => User::class
            ])
            ->add('trabajador_entrante', EntityType::class, [
                'class' => User::class
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cuadre::class,

        ]);
    }
}
