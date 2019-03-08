<?php

namespace Acme\BackendBundle\Form\Type\User;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Acme\BackendBundle\Form\Model\CambiarContrasenaModel;

class ChangePasswordUserType extends AbstractType
{
    protected $dataClass = 'Acme\BackendBundle\Form\Model\User\CambiarContrasenaModel';
    protected $doctrine = null;
     
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        parent::buildForm($builder, $options);
         
        $builder
            ->add('username', null, array(
                'label' => 'form.username', 
                'translation_domain' => 'FOSUserBundle'
              ))
        ;
        
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array(
                'label' => 'form.new_password',
                'attr' =>  array('placeholder' => 'Nueva contraseña')
            ),
            'second_options' => array(
                'label' => 'form.new_password_confirmation',
                'attr' =>  array('placeholder' => 'Repita la nueva contraseña')
            ),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'acme_backendbundle_edit_user_type';
    }
}
