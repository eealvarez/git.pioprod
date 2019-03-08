<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ConexionCompuestaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_conexion_compuesta_admin';
    protected $baseRoutePattern = 'conexioncompuesta';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('fechaViaje', 'datetime', array(
            'label' => 'Fecha',
            'format' => 'd-m-Y h:i A'
        ))
        ->add('itinerarioCompuesto', null, array('label' => 'Itinerario'))
        ->add('activa', null, array('label' => 'Activa'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('itinerarioCompuesto', null, array('label' => 'Itinerario'))
        ;
    }
    
    protected function configureFormFields(FormMapper $mapper)
    {
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        }
        
        $mapper
        ->with('General')
            ->add('id', null, array(
                'label' => 'ID',
                'read_only' => true
            ))
            ->add('fechaViaje', 'datetime', array(
                'label' => 'Fecha',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy HH:mm',
                'widget' => 'single_text',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('itinerarioCompuesto', null, array(
                'label' => 'Itinerario Compuesto',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('listaConexionItem', null, array(
                'label' => 'Conexiones Simples',
                'read_only' => true,
                'disabled' => true,
            ))
            ->add('activa', null, array(
                'required' => false,
                'label' => 'Activa'
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
