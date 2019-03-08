<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Acme\BackendBundle\Entity\Job;

class JobAdmin extends Admin
{
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_job_admin';
    protected $baseRoutePattern = 'job';
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper   
//            ->add('name')
//            ->add('serviceId')
//            ->add('tags', null, array(), null, array(
//                'multiple' => true,
//            ))
//            ->add('nextExecutionDate', 'doctrine_orm_date_range')
            ->add('status', 'doctrine_orm_choice', array(), 'choice',array(
                'choices' => array(
                    Job::STATUS_FAILED => 'Failed',
                    Job::STATUS_RUNNING => 'Running',
                    Job::STATUS_WAITING => 'Waiting',
                    Job::STATUS_TERMINATED => 'Ended',
                ),
            ))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('serviceId')
            ->add('lastExecutionDate', null, array(
                'format' => 'd/m/Y H:i:s'
            ))
            ->add('repeatEvery')
            ->add('nextExecutionDate', null, array(
                'format' => 'd/m/Y H:i:s'
            ))
            ->add('executionCount')
            ->add('status')
//            ->add('lastExceptionToString', 'text', array(
//               'label' => 'Last Exception'
//            ))
        ;
    }
    
    protected function configureFormFields(FormMapper $mapper)
    {
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        }
        
        $mapper->with('General')
           ->add('status', 'choice', array(
                'choices' => array(
                    Job::STATUS_FAILED => 'Failed',
                    Job::STATUS_RUNNING => 'Running',
                    Job::STATUS_WAITING => 'Waiting',
                    Job::STATUS_TERMINATED => 'Ended',
                ),
            ))
            ->add('repeatEvery', 'text', array(
               'required' => false
            ))
           ->add('executionCount', null, array(
               'required' => true
            ))
           ->add('lastException', 'textarea', array(
               'label' => 'Last Exception',
               'required' => false,
               'read_only' => true,
               'disabled' => true
            ))

        ->end();
        
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
//        $collection->remove('edit');
        $collection->remove('create');
    }
}
