<?php

namespace App\Form;

use App\Entity\Configuracion;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpcionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ganaciaMinPedido', MoneyType::class, [
                'label' => "Ganancia mínima de un pedido",
                'currency' => 'USD'
            ])
            ->add('pagosaldo', CheckboxType::class, [
                'label' => 'Pago por saldo del usuario',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('pagocash', CheckboxType::class, [
                'label' => 'Pago en efectivo',
                'required' => false
            ])
            ->add('pagopaypal', CheckboxType::class, [
                'label' => 'Pagos con PayPal',
                'required' => false
            ])
            ->add('cambiocup', MoneyType::class, [
                'label' => 'Cambio CUP por cada USD',
                'currency' => 'CUP'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuracion::class,
        ]);
    }
}
