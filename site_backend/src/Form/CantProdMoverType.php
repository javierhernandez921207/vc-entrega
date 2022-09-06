<?php

namespace App\Form;

use App\Entity\Negocio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CantProdMoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('negocio', EntityType::class, [
                'class' => Negocio::class
            ])
            ->add('cantidad_mover', \Symfony\Component\Form\Extension\Core\Type\NumberType::class,
                ['label' => 'Unidades a mover', 'attr' => ['placeholder' => 'Unidades a mover']])
            ->add('submit', SubmitType::class, [
                'label' => 'Guardar']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
