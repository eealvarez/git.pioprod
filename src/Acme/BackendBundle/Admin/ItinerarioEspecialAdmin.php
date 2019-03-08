<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ItinerarioEspecialAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_itinerario_especial_admin';
    protected $baseRoutePattern = 'itinerarioespecial';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('fecha', null, array(
            'label' => 'Fecha',
            'format' => 'd-m-Y h:i A'
        ))
        ->add('estacion', null, array('label' => 'Estación'))
        ->add('idExterno', null, array('label' => 'Id Externo'))
        ->add('ruta', null, array('label' => 'Ruta'))
        ->add('tipoBus', null, array('label' => 'Tipo Bus'))     
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('idExterno', null, array('label' => 'Id Externo'))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('fecha', null, array('label' => 'Fecha'))
            ->add('estacion', null, array('label' => 'Estación'))
            ->add('idExterno', null, array('label' => 'Id Externo'))
            ->add('ruta', null, array('label' => 'Ruta'))
            ->add('tipoBus', null, array('label' => 'Tipo Bus'))       
            ->add('activo', null, array('label' => 'Activo'))    
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
