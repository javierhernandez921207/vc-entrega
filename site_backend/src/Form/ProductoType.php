<?php

namespace App\Form;

use App\Entity\Producto;
use Doctrine\DBAL\Types\IntegerType as IntegerTypeAlias;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class, ['attr' => ['placeholder' => 'Nombre del producto.']])
            ->add('precio', MoneyType::class, ['label' => "Precio de venta",
                'currency' => 'USD', 'attr' => ['placeholder' => 'Precio de venta del producto.']])
            ->add('costo', MoneyType::class, ['label' => "Costo del producto",
                'currency' => 'USD', 'attr' => ['placeholder' => 'Precio por el que el almacén obtuvo el producto.']])
            ->add('descr', TextareaType::class,
                ['label' => "Descripción",
                    'attr' => [
                        'cols' => 80,
                        'rows' => 8,
                        'placeholder' => 'Descripción del producto'
                    ]])
            ->add('cantidad', IntegerType::class, ['attr' => ['placeholder' => 'Unidades físicas en el almacén.']])
            ->add('cantMin', IntegerType::class, ['label' => "Cantidad mínima", 'attr' => ['required' => false, 'placeholder' => 'Unidades físicas necesarias en el almacén.']])
            ->add('imagen', FileType::class, [
                'label' => 'Imagen del producto',
                'attr' => ['placeholder' => 'Buscar imagen'],
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Seleccione un archivo imagen.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
