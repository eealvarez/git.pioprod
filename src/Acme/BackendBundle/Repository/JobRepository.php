<?php

namespace Acme\BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Acme\BackendBundle\Entity\Job;

class JobRepository extends EntityRepository
{
    public function getExecutableJobs()
    {
        $now = new \DateTime('now');
        $now = $now->format('d-m-Y H:i:s');
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.nextExecutionDate <= :date')
            ->andWhere('t.status = :status')
            ->orderBy('t.nextExecutionDate')
            ->setParameters(array(
                'date'   => $now,
                'status' => Job::STATUS_WAITING,
            ))
        ;

        return $qb->getQuery()->getResult();
    }

    public function getRemovableJobs()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.status = :status')
            ->setParameters(array(
                'status' => Job::STATUS_TERMINATED,
            ))
        ;

        return $qb->getQuery()->getResult();
    }

    public function getFailedJobs()
    {
        $time = new \DateTime('30 minutes');
        $time = $time->format('d-m-Y H:i:s');
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where($qb->expr()->orX(
                $qb->expr()->eq('t.status', "'".Job::STATUS_FAILED."'"),
                $qb->expr()->andX(
                    $qb->expr()->eq('t.status', "'".Job::STATUS_RUNNING."'"),
                    $qb->expr()->lte('t.lastExecutionDate', ':date')
            )))
            ->setParameter('date', $time)
        ;

        return $qb->getQuery()->getResult();
    }
    
    public function getRunningJobs()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where($qb->expr()->eq('t.status', "'".Job::STATUS_RUNNING."'"));
        return $qb->getQuery()->getResult();
    }

    /**
     * @param array$tags
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQueryBuilderForTags($tags)
    {
        $qb = $this->createQueryBuilder('j');

        return $qb
            ->select('j,t')
            ->join('j.tags', 't')
            ->where($qb->expr()->in('t.name', $tags))
            ->groupBy('j')
            ->having('count(t) = ' . count(array_unique($tags)))
        ;
    }
}
