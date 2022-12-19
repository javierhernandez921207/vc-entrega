<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntradaProdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'entrada',
                \Symfony\Component\Form\Extension\Core\Type\IntegerType::class,
                ['label' => 'Unidades', 'attr' => ['placeholder' => 'Unidades a dar entrada']]
            )
            ->add('costo', MoneyType::class, [
                'label' => "Costo de entrada",
                'currency' => 'USD', 'attr' => ['placeholder' => 'Costo de entrada del producto.']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
