<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class GaleriaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_galeria_admin';
    protected $baseRoutePattern = 'galeria';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre')
        ->add('orden')        
        ->add('referencia')
        ->add('descripcion')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('nombre')
        ->add('referencia')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->add('nombre')
        ->add('referencia')
        ->add('descripcion', 'textarea', array('required' => false))
        ->add('orden')
        ->add('activo', null,  array('required' => false))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
}

?>
