<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository {

    public function getLatestEventsByProjects($projects) {


        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Project', 'Log', 'Task')
                ->from('AppBundle\Entity\Log', 'Log')
                ->leftJoin('Log.task', 'Task')
                ->leftJoin('Task.project', 'Project')
                ->where('Project.id IN (:projects)')
                ->orderBy('Log.date', 'DESC')
                ->setParameter('projects', $projects)
                ->setMaxResults(12)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getTaskLatestEventsByProjects($project_id, $task_id) {


        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Log', 'Task')
                ->from('AppBundle\Entity\Log', 'Log')
                ->leftJoin('Log.task', 'Task')
                ->where('Task.project = :project')
                ->andWhere('Task.id = :task')
                ->setParameter('project', $project_id)
                ->setParameter('task', $task_id)
                ->setMaxResults(10)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

}
