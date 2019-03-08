<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ItinerarioCompuestoAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_itinerario_compuesto_admin';
    protected $baseRoutePattern = 'itinerariocompuesto';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('diaSemana', null, array('label' => 'Dia'))
        ->add('horarioCiclico', null, array('label' => 'Horario'))
        ->add('estacionOrigen', null, array('label' => 'Origen'))
        ->add('estacionDestino', null, array('label' => 'Destino'))    
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('diaSemana', null, array('label' => 'Dia'))
        ->add('horarioCiclico', null, array('label' => 'Horario'))
        ->add('estacionOrigen', null, array('label' => 'Origen'))
        ->add('estacionDestino', null, array('label' => 'Destino')) 
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
}

?>
