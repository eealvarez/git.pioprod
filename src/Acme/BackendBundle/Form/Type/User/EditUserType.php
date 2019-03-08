<?php

namespace Acme\BackendBundle\Form\Type\User;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class EditUserType extends AbstractType
{
    protected $dataClass = 'Acme\BackendBundle\Entity\User';
    protected $doctrine = null;
     
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
         parent::buildForm($builder, $options);
         
         $edit = $options['edit'];
         $builder
            ->add('names', null, array('label' => 'Nombres'))
            ->add('surnames', null, array('label' => 'Apellidos'))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('phone', null, array('label' => 'Teléfono'))
            ->add('intentosFallidos', null, array('label' => 'Intentos Fallidos'))
            ->add('enabled', 'checkbox', array('label' => 'Activo', 'required' => false))
            ->add('locked', 'checkbox', array('label' => 'Bloqueado', 'required' => false))
            ->add('lastLogin', 'datetime', array(
                'label' => 'Último Acceso',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
            ->add('expired', 'checkbox', array('label' => 'Expiro el acceso', 'required' => false))
            ->add('expiresAt', 'datetime', array(
                'label' => 'Fecha Expiración de Acceso',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
            ->add('credentialsExpired', 'checkbox', array('label' => 'Expiro la contraseña', 'required' => false))
            ->add('credentialsExpireAt', 'datetime', array(
                'label' => 'Fecha Expiración de Contraseña',
                'required'    => false,
                'read_only' => true,
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm:ss',
            ))
         ;
         
         $builder->add('estaciones', 'entity', array(
            'label' => 'Estaciones',
            'property' => 'aliasNombre',
            'class' => 'Acme\MayaBundle\Entity\Estacion',
            'multiple'  => true,
            'expanded'  => true,
            'required'    => false
         ));
         
         $repository = $this->doctrine->getRepository('BackendBundle:Rol'); 
         $listaRoles = $repository->findAll();
         $choices = array();
         foreach ($listaRoles as $rol){
             $choices[$rol->getNombre()] = $rol->getNombre() . " - " . $rol->getDescripcion();
         }
         
         $builder->add('roles' , "choice",  array(
             'label' => 'Roles',
             'multiple'  => true,
             'expanded'  => true,
             'choices'   => $choices,
             'required'    => true
          ));
         
         $builder->add('ipRanges', 'collection', array(
            'label' => 'Rangos de Ip',
            'type'   => 'text',
            'prototype' => true,
            'prototype_name' => 'Ip',
            'allow_add'    => true,
            'allow_delete'    => true,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
            'cascade_validation' => true
        ))->setRequired(array(
            'edit',
        ));
    }

    public function getName()
    {
        return 'acme_backendbundle_edit_user_type';
    }
}
