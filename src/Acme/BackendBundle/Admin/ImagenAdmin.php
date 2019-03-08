<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ImagenAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_galeria_imagen_admin';
    protected $baseRoutePattern = 'galeriaimagen';
    
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('referencia')
        ->add('nombre')
        ->add('galeria')
        ->add('descripcion')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('galeria')
        ->add('referencia')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $editar = false;
        if(strpos($this->request->getPathInfo(), "edit")){
              $editar = true;
        }
        
        $entity = $this->getSubject();
        
        $mapper
            ->add('galeria')
            ->add('referencia')
            ->add('nombre')
            ->add('ancho', null, array('label' => 'Ancho (px)'))
            ->add('alto', null, array('label' => 'Alto (px)'))
            ->add('descripcion', 'textarea', array('required' => false))
            ->add('url')
        ;
        
         
        $options = array(
            'label' => 'Imagen',
            'required' => $editar === false ? true : false
        );
        if($editar === true && $entity->getImagenPequena() !== null){
             $options['help'] = '<img src="data:image/jpg;base64,' . $entity->getImagenPequena() . '" />';
        }
        
        $mapper->add('file', 'file', $options)
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        
    }
}

?>
