<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository {

    public function getLatestEventsByProjects($projects) {


        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Project','Log', 'Task' )
                ->from('AppBundle\Entity\Log', 'Log')
                ->leftJoin('Log.task', 'Task')
                ->leftJoin('Task.project', 'Project')
                ->where('Project.id IN (:projects)')
                ->setParameter('projects', $projects)
                ->setMaxResults(20)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

}
