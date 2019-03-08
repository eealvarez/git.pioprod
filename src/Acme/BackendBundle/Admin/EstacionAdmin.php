<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EstacionAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_estacion_admin';
    protected $baseRoutePattern = 'estacion';
    protected $formOptions = array('cascade_validation' => true);
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre')
        ->add('direccion', null, array('label' => 'Dirección'))
        ->add('alias')  
        ->add('tipo.nombre', null, array('label' => 'Tipo estación'))
        ->add('longitude')   
        ->add('latitude')
        ->add('facturacion', null, array('label' => 'Entrega Factura'))
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('nombre')
        ->add('alias')  
        ->add('tipo.nombre', null, array('label' => 'Tipo estación'))
        ->add('facturacion', null, array('label' => 'Entrega Factura'))         
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->with('General')
            ->add('id', null, array(
                'label' => 'ID',
                'read_only' => true
            ))
            ->add('alias', null, array(
                'label' => 'Alias',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('nombre', null, array(
                'label' => 'Nombre',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('direccion', null, array(
                'label' => 'Dirección',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('tipo', null, array(
                'label' => 'Tipo',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('listaTelefonos', 'text', array(
                'label' => 'Teléfonos',
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('listaCorreos', 'text', array(
                'label' => 'Correos',
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('longitude', 'text', array(
                'label' => 'Longitude',
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('latitude', 'text', array(
                'label' => 'Latitude',
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('facturacion', null, array(
                'required' => false,
                'label' => 'Entrega Factura'
            ))
            ->add('activo', null,  array(
                'required' => false,
                'read_only' => true,
                'disabled' => true,
            ))
        ->end();
        
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
    }
    
}

?>
