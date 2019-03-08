<?php

namespace Acme\BackendBundle\Form\Type\Compra;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FacturarCompraType extends AbstractType
{
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
//        $entity = $builder->getData();
        
        $builder
            ->add("id", 'hidden')
            ->add('codigoCompra', 'text', array(
                'label' => 'Código Compra',
                'read_only' => true,
                'disabled' => true
            ))
            ->add("fecha", 'date', array(
                'label' => 'Fecha',
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
            ))
            ->add("serieFactura", 'text', array(
                'label' => 'Serie'
            ))
            ->add("correlativoFactura", 'text', array(
                'label' => 'Correlativo'
            ))
            ->add("observacionesFactura", 'textarea', array(
                'label' => 'Observaciones adicionales',
                'required' => false
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\MayaBundle\Entity\Compra',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'backendbundle_facturar_compra_type';
    }
}
