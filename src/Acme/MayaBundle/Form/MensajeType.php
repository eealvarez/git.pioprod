<?php
namespace Acme\MayaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;

class MensajeType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        
        $builder->add('nombre', 'text',  array(
            'label' => 'Nombre Completo',
            'required' => true,
        ));
        
        $builder->add('correo', 'email',  array(
            'label' => 'Correo',
            'required' => true,
        ));
        
        $builder->add('mensaje', 'textarea',  array(
            'label' => 'Mensaje',
            'required' => true,
        ));
        
//        $builder->add('recaptchaMensaje', 'ewz_recaptcha', array(
//            'mapped' => false,
//            'constraints' => array(
//                new True()
//            )
//        ));
   }

     public function getName()
    {
        return 'mensaje_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\MayaBundle\Entity\Mensaje',
 	    'cascade_validation' => true,
            'csrf_protection'   => true
    	));
    }
}

?>
