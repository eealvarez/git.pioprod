<?php

namespace Acme\BackendBundle\Form\Type\ItinerarioCompuesto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ItinerarioCompuestoType extends AbstractType
{
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $entity = $builder->getData();
        $edit = $options['edit'];
        
        if($edit){
            $builder
                 ->add('id', null, array(
                    'label' => 'Id',
                    'read_only' => $edit,
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('diaSemana', null, array(
                    'label' => 'Dia Semana',
                    'read_only' => $edit,
                    'disabled' => $edit,
                    'attr' =>  array('class' => 'span6')
                ))
                ->add('horarioCiclico', 'entity', array(
                    'class'=>'MayaBundle:HorarioCiclico',
                    'label' => 'Horario',
                    'required' => true,
                    'multiple'  => false,
                    'expanded'  => false,
                    'read_only' => $edit,
                    'disabled' => $edit,
                    'attr' =>  array('class' => 'span6'),
                    'query_builder' => function(EntityRepository $er){
                        $query = $er->createQueryBuilder('e')
                                    ->orderBy('e.hora');       
                        return $query;
                    }
                ));
        }        
        
        $builder
                ->add('estacionOrigen', 'entity' , array(
                    'class'=>'MayaBundle:Estacion',
                    'label' => 'Estación de Origen',
                    'required' => true,
                    'multiple'  => false,
                    'expanded'  => false,
                    'read_only' => $edit,
                    'disabled' => $edit,
                    'attr' =>  array('class' => 'span6'),
                    'query_builder' => function(EntityRepository $er){
                        $query = $er->createQueryBuilder('e')
                                    ->orderBy('e.nombre');       
                        return $query;
                    }
                ));
                
        if($edit){
            $builder 
                ->add('estacionDestino', 'entity' , array(
                    'class'=>'MayaBundle:Estacion',
                    'label' => 'Estación de Destino',
                    'required' => true,
                    'multiple'  => false,
                    'expanded'  => false,
                    'read_only' => true,
                    'disabled' => true,
                    'attr' =>  array('class' => 'span6'),
                    'query_builder' => function(EntityRepository $er){
                        $query = $er->createQueryBuilder('e')
                                    ->orderBy('e.nombre');       
                        return $query;
                    }
                ));        
         }
        
         $builder
                 ->add('idEstacionDestino', 'hidden', array(
                    'data' => "",
                    'mapped' => false
                ))
                ->add('activo', null, array(
                    'required' => false
                ))
            ;
                
        $itemsOrderJson = array();
        $itemsOrder = $entity->getListaItinerarioItemOrder();
        foreach ($itemsOrder as $element) {
            $item = new \stdClass();
            $item->id = $element->getId();
            $item->orden = $element->getOrden();
            $item->idEstacion = $element->getBajaEn()->getId();
            $item->nombreEstacion = $element->getBajaEn()->getAliasNombre();
            $listaItinerariosSimples = array();
            
            $clave = "";
            $idDiaSemana = "";
            $codigoRuta = "";
            $listaItinerarioSimple = $element->getListaItinerarioSimple();
            foreach ($listaItinerarioSimple as $itinerariosSimple) {
                if($clave === "") $clave = $itinerariosSimple->getClave1();
                if($idDiaSemana === "") $idDiaSemana = strval($itinerariosSimple->getDiaSemana()->getId());
                if($codigoRuta === "") $codigoRuta = $itinerariosSimple->getRuta()->getCodigo();
                $listaItinerariosSimples[] = array(
                    'idItinerario' => $itinerariosSimple->getId(),
                    'nombreItinerario' => $itinerariosSimple->getInfo1(),
                    'clave' => $clave,
                    'idDiaSemana' => $idDiaSemana,
                    'codigoRuta' => $codigoRuta
                );
            }
            $item->listaItinerariosSimples = $listaItinerariosSimples;
            $item->clave = $clave;
            $item->idDiaSemana = $idDiaSemana;
            $item->codigoRuta = $codigoRuta;
            $itemsOrderJson[] =  $item;
        }
//        var_dump(json_encode($itemsOrderJson));
        $builder->add('listaParadasIntermediasHidden', 'hidden', array(
            'data' => json_encode($itemsOrderJson),
            'mapped' => false
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\MayaBundle\Entity\ItinerarioCompuesto',
            'cascade_validation' => true,
        ))->setRequired(array(
            'edit',
        ));
    }

    public function getName()
    {
        return 'backendbundle_itinerario_compuesto_type';
    }
}
