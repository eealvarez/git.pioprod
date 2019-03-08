<?php
namespace Acme\MayaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\MayaBundle\Form\DataTransformer\EstacionToNumberTransformer;

class BuscarConexionesType extends AbstractType{

     
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        $entityManager = $options['em'];
        
        $builder->add('estacionOrigen', 'text',  array(
            'label' => 'Estación Origen',
            'required' => true,
            'attr' =>  array('class' => 'form-control sinPadding')
        ));
        $builder->get('estacionOrigen')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('estacionDestino', 'text',  array(
            'label' => 'Estación Destino',
            'required' => true,
            'attr' =>  array('class' => 'form-control sinPadding')
        ));
        $builder->get('estacionDestino')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('cantidadPasajeros', 'choice',array(
           'label' => 'Pasajeros',
           'empty_value' => "",
           'empty_data'  => null,
           'choices' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8),
           'attr' =>  array('class' => 'form-control sinPadding')
        ));
               
        $builder->add('fechaSalida', 'date',  array(
            'label' => 'Fecha de ida',
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'form-control textCenter')
        ));
        
         $builder->add('fechaRegreso', 'date',  array(
            'label' => 'Fecha de regreso',
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'form-control textCenter')
        ));
        
        $builder->add('conexionesDirectas', 'checkbox', array(
           'label' => 'Viajes directos',
           'required' => false
        ));
       
       $builder->add('idaRegreso', 'choice', array(
            'expanded' => true,
            'required' => true,
            'multiple' => false,
            'choices' => array('false' => 'Solo ida', 'true' => 'Ida y regreso'),
            'empty_value' => false,
        ));
       
       $optionEstaciones = array();
       $mapDepartamentoEstacion = array();
       $estacionesActivas = $entityManager->getRepository('MayaBundle:Estacion')->getEstacionesActivasByDepartamento();
       foreach ($estacionesActivas as $estacion) {
            $departamento = $estacion->getDepartamento();
            $nombreDepartamento = ($departamento !== null) ? $departamento->getNombre() : "";
            if(!isset($mapDepartamentoEstacion[$nombreDepartamento])){
                $mapDepartamentoEstacion[$nombreDepartamento] = array();
            }
            $mapDepartamentoEstacion[$nombreDepartamento][] = $estacion;
       }
       foreach ($mapDepartamentoEstacion as $nombreDepartamento => $listaEstaciones) {
            $listaTemp = array();
            foreach ($listaEstaciones as $estacion) {
                $listaTemp[] = array(
                    "id" => $estacion->getId(),
                    "text" => $estacion->getNombre(),
                );
            }
            $optionEstaciones[] = array(
                "text" => strtoupper($nombreDepartamento),
                "children" => $listaTemp
            );
       }
       $builder->add("listaEstaciones", 'hidden', array(
           'mapped' => false,
           'data' => json_encode($optionEstaciones),
       ));
       
   }

     public function getName()
    {
        return 'buscar_conexiones_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\MayaBundle\Form\Model\BuscarConexionesModel',
 	    'cascade_validation' => true,
            'csrf_protection'   => false
    	))->setRequired(array(
            'em'
        ))
        ->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
