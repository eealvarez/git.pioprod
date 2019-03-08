<?php

namespace Acme\BackendBundle\Scheduled;

use Acme\BackendBundle\Entity\Job;

trait ScheduledServiceTrait
{
    /**
     * @var Job
     */
    protected $job;

    public function setScheduledJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }
}
