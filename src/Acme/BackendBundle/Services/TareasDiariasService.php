<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Entity\Notificacion;

class TareasDiariasService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $job;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array();
        $this->job = null;
    }   
    
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function metod1($options = null){
        
    }
    
    
    public function setScheduledJob(Job $job = null) {
        $this->logger->warn("TareasDiariasService - init");
        
        $this->job = $job;
        
        try {
            $this->logger->warn("start-metod1");
            $this->metod1();
            $this->logger->warn("end-metod1");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso metod1.");
            throw $ex;
        }
        
        $this->logger->warn("TareasDiariasService - end");
    }
    
}
