<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Rol', ChoiceType::class, [
                'choices' => [
                    'Cliente' => 'ROLE_USER',
                    'Trabajador' => 'ROLE_TRAB',
                    'Trabajador solo negocios' => 'ROLE_TRABN',
                    'Administrador' => 'ROLE_ADMIN',
                ],
                'label' => "Rol en el sistema",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
