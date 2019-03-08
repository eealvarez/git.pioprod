<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ConexionSimpleAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_conexion_simple_admin';
    protected $baseRoutePattern = 'conexionsimple';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('fechaViaje', null, array(
            'label' => 'Fecha',
            'format' => 'd-m-Y h:i A'
        ))
        ->add('itinerario', null, array('label' => 'Itinerario'))
        ->add('idExterno', null, array('label' => 'Id Externo'))  
        ->add('estado', null, array('label' => 'Estado'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('estado')
        ->add('idExterno')
        ->add('itinerario')
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('fechaViaje', null, array(
                'label' => 'Fecha',
                'format' => 'd-m-Y h:i A'
            ))
            ->add('itinerario', null, array('label' => 'Itinerario'))
            ->add('idExterno', null, array('label' => 'Id Externo'))
            ->add('tipoBus', null, array('label' => 'Tipo Bus'))
            ->add('estado', null, array('label' => 'Estado'))
        ->end();
    }
    
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->add('show');
    }
}

?>
