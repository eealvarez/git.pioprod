<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class RolAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_rol_admin';
    protected $baseRoutePattern = 'rol';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('nombre', null, array('label' => 'ID'))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('nombre')
        ->add('descripcion', null, array('label' => 'Descripción'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $editarCodigo = false;
        if(strpos($this->request->getPathInfo(), "edit")){
              $editarCodigo = true;
        }  
        if($editarCodigo){
            $mapper
            ->add('descripcion', null,  array('label' => 'Descripción'))
            ;
        }else{
            $mapper
            ->add('nombre')
            ->add('descripcion', null,  array('label' => 'Descripción'))
            ;
        }
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
