<?php

namespace Acme\BackendBundle\Form\Type\Compra;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnviarFacturaType extends AbstractType
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
        return 'backendbundle_enviar_factura_type';
    }
}
