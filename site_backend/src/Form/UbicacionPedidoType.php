<?php

namespace App\Form;

use App\Entity\Transporte;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UbicacionPedidoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', null, ['label' => 'Nombre de familiar o amigo.', 'required' => true])
            ->add('ci', null, ['label' => 'Carnet de identidad.', 'required' => true])
            ->add('telefono', null, ['label' => 'Teléfono de contacto.', 'required' => true])
            ->add('direccion', TextareaType::class, ['label' => 'Dirección de entrega.', 'required' => true]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
