<?php

namespace Acme\BackendBundle\Scheduled;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Entity\JobTag;
use ScheduledServiceInterface;

/**
 * Description of Scheduler
 *
 * @author jerome
 */
class Scheduler implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    protected function log($level, $message)
    {
        $logger = $this->container->get('logger');
        $logger->log($level, $message);

        if (isset($this->output)) {
            $this->output->writeln(sprintf('<info>%s</info>', $message));
        }
    }

    /**
     * @param string $serviceId
     * @return \Jerive\Bundle\SchedulerBundle\Entity\Job
     * @throws \RuntimeException
     */
    public function createJob($serviceId, $name = null)
    {
        $service = $this->container->get($serviceId);
        $job = new Job();
        $job->setServiceId($serviceId)
            ->setName($name)
            ->setProxy($this->container->get('acme_scheduler.proxy')->setService($service));
        
        return $job;
    }

    /**
     * Process the tags added to the entity
     *
     * @param \Jerive\Bundle\SchedulerBundle\Entity\Job $job
     */
    protected function processTags(Job $job)
    {
        $names = array();
        $collection = $job->getTags();

        if ($collection->count()) {
            foreach($job->getTags() as $key => $tag) {
                if (!$tag->getId()) {
                    unset($collection[$key]);
                    $names[$tag->getName()] = $tag->getName();
                }
            }

            $qb = $this->container->get('doctrine')->getManager()->getRepository('BackendBundle:JobTag')->createQueryBuilder('t');
            $qb->where($qb->expr()->in('t.name', array_values($names)));

            foreach($qb->getQuery()->getResult() as $tag) {
                unset($names[$tag->getName()]);
                $collection->add($tag);
            }

            foreach($names as $name) {
                $tag = new JobTag();
                $tag->setName($name);
                $collection->add($tag);
            }
        }
    }

    /**
     * @param Job $job
     * @return Scheduler
     */
    public function schedule(Job $job)
    {
        $this->processTags($job);

        $this->getManager()->persist($job);
        $this->getManager()->flush($job);

        return $this;
    }

    /**
     * @return Scheduler
     */
    public function executeJobs()
    {
        foreach($this->getJobRepository()->getExecutableJobs() as $job) {
            
            $this->log(Logger::WARNING, "Scheduler START: " . $job->getName());
            $job->prepareForExecution();
            $this->getManager()->persist($job);
            $this->getManager()->flush($job);
            
            $check = true;
            while ($check) {
                try {
                    $job->getProxy()->setDoctrine($this->container->get('doctrine'));
                    $check = $job->execute($this->container->get($job->getServiceId()));
                    $this->log(Logger::INFO, sprintf('SUCCESS [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));
                } catch (\Exception $e) {
                    $this->log(Logger::ERROR, sprintf('FAILURE [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));
                    $this->log(Logger::ERROR, sprintf(
                        "FAILURE %s: %s (uncaught exception) at %s line %s while running console command '%s'",
                        get_class($e),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                        $job->getName()
                    ));
                    $check = false;
                    
                    $emailsAdmin = $this->getManager()->getRepository('BackendBundle:User')->findEmailSuperAdmin();
                    if(count($emailsAdmin) != 0){
                        $now = new \DateTime();
                        $now = $now->format('Y-m-d H:i:s');
                        $subject = $now . ". El proceso " . $job->getName() . " acaba de fallar, requiere atención inmediata."; 
                        $body = "Detalles del fallo:\n";
                        $body.= sprintf(
                            "FAILURE %s: %s (uncaught exception) at %s line %s while running console command '%s'",
                            get_class($e),
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine(),
                            $job->getName()
                        );
                        $message = \Swift_Message::newInstance()
                         ->setSubject($subject)
                         ->setFrom($this->container->getParameter("mailer_user"))
                         ->setTo($emailsAdmin)
                         ->setBody($body);
                         $this->container->get('mailer')->send($message);
                    }
                }
                $this->getManager()->persist($job);
                $this->getManager()->flush($job);
            }
        }

        return $this;
    }

    /**
     * @return Scheduler
     */
    public function cleanJobs()
    {
        foreach($this->getJobRepository()->getRemovableJobs() as $job) {
            $this->getManager()->remove($job);
            $this->log(Logger::INFO, sprintf('REMOVE job [%s]#%s', $job->getName(), $job->getId()));
        }

        $this->getManager()->flush();
        return $this;
    }

    /**
     * @param array $tags
     * @param array $criteria
     * @return array
     */
    public function findByTags($tags, $criteria = array())
    {
        $qb = $this->getJobRepository()->getQueryBuilderForTags($tags);

        foreach($criteria as $key => $value) {
            $qb->andWhere('j.' . $key . ' = :' . $key)->setParameter($key, $value);
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     *
     * @param object $entity
     * @param array $tags
     * @param array $criteria
     * @return Array
     */
    public function findByEntityTag($entity, $tags = array(), $criteria = array())
    {
        if (!is_array($tags)) {
            $tags = array($tags);
        }

        $tags[] = $this->container->get('acme_scheduler.proxy')->getTagForEntity($entity);
        return $this->findByTags($tags, $criteria);
    }

    /**
     * @return \Jerive\Bundle\SchedulerBundle\Entity\Repository\JobRepository
     */
    protected function getJobRepository()
    {
        return $this->getManager()->getRepository('BackendBundle:Job');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}
