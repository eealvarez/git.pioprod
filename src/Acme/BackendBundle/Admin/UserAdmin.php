<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_user_admin';
    protected $baseRoutePattern = 'user';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('username', null, array('label' => 'Username'))
        ->add('names', null, array('label' => 'Nombres'))
        ->add('surnames', null, array('label' => 'Apellidos'))
        ->add('email', null, array('label' => 'Correo'))
//        ->add('empresas', null, array('label' => 'Empresas'))
        ->add('lastLogin', null, array('label' => 'Última Autenticación'))
        ->add('enabled', null, array('label' => 'Activo'))                
        ->add('locked', null, array('label' => 'Bloqueado'))
        ->add('expired', null, array('label' => 'Expiro Acceso'))
        ->add('credentialsExpired', null, array('label' => 'Expiro Credenciales'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'Identificador'))
        ->add('username', null, array('label' => 'Username'))
        ->add('names', null, array('label' => 'Nombres'))
        ->add('surnames', null, array('label' => 'Apellidos'))
        ->add('email', null, array('label' => 'Correo'))
//        ->add('lastLogin', null, array('label' => 'Última Autenticación'))
        ->add('locked', null, array('label' => 'Bloqueado'))
        ->add('enabled', null, array('label' => 'Activo'))
        ->add('expired', null, array('label' => 'Expiro Acceso'))
        ->add('credentialsExpired', null, array('label' => 'Expiro Credenciales'))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->add('changePassword', 'changePassword');
    }
    
}

?>
