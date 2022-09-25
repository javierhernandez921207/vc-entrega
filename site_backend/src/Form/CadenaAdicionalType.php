<?php

namespace App\Form;

use App\Entity\Configuracion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CadenaAdicionalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('cadena', null, [
                'label' => 'Operaciones Adicionales CUP',
                'attr' => ['placeholder' => '+ 100 - 200 + 400.25']
            ])
            ->add('cadenaUSD', null, [
                'label' => 'Operaciones Adicionales USD',
                'attr' => ['placeholder' => '+ 100 - 200 + 400.25']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuracion::class,
        ]);
    }
}
