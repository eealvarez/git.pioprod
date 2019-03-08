<?php
namespace Acme\MayaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;



class ClienteAnonimoType extends AbstractType{

     
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        $builder->add('correo', 'repeated', array(
            'type' => 'email',
            'invalid_message' => 'La dirección de correo debe coincidir.',
            'options' => array('attr' => array('class' => 'form-control')),
            'required' => true,
            'first_options'  => array('label' => 'Correo'),
            'second_options' => array('label' => 'Confirmar correo'),
        ));
        
       
        $builder->add('nombreApellidos', 'text',  array(
            'label' => 'Nombre y Apellidos',
            'attr' =>  array('class' => 'form-control')
        ));
        
       $builder->add('telefono', null, array(
           'label' => 'Teléfono',
           'max_length'=>21,
           'required' => false,
           'attr' =>  array('class' => 'form-control')
           ));
       
       $builder->add('numeroDocumento', null, array(
           'label' => 'No. de documento',
           'max_length'=>40,
           'required' => true,
           'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('nit', null, array(
            'label' => 'NIT',
            'max_length'=>20,
            'required' => false,
            'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('tipoDocumento', 'entity',  array(
            'class' => 'MayaBundle:Documento',
            'label' => 'Tipo de documento',
            'property' => 'tipo',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('sexo', 'entity',  array(
            'class' => 'MayaBundle:Sexo',
            'label' => 'Sexo',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'property' => 'nombre',
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('nacionalidad', 'entity',  array(
            'class' => 'MayaBundle:Nacionalidad',
            'label' => 'Nacionalidad',
            'property' => 'nombre',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('fechaVencimientoDocumento', 'date',  array(
            'label' => 'Fecha venc. del documento',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' =>  array('class' => 'form-control')
        ));
       
       $builder->add('fechaNacimiento', 'date',  array(
            'label' => 'Fecha de nacimiento',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' =>  array('class' => 'form-control')
        ));
        
   }

     public function getName()
    {
        return 'cliente_anonimo';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\MayaBundle\Entity\Cliente',
 	    'cascade_validation' => true,
            'csrf_protection'   => false
    	));
    }
}

?>
