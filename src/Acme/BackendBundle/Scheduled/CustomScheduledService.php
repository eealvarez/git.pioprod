<?php

namespace Acme\BackendBundle\Scheduled;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
//use Acme\BackendBundle\Scheduled\ScheduledServiceTrait;
use Acme\BackendBundle\Entity\Job;

class CustomScheduledService implements ScheduledServiceInterface{
    
    public function setScheduledJob(Job $job) {
        var_dump("CustomScheduledService --- init ");
    }
    
}
