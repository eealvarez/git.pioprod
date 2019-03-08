<?php

namespace Acme\BackendBundle\Scheduled;

use Acme\BackendBundle\Entity\Job;

interface ScheduledServiceInterface
{
    /**
     *
     * @param Job $job
     */
    public function setScheduledJob(Job $job);
}

