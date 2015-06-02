<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TaskRepository extends EntityRepository {

    public function getProjectTaskById($project_id, $task_id) {
        return $this->getEntityManager()
                        ->createQuery("select s from AppBundle\Entity\Task s where "
                                . "s.project = $project_id and s.id = $task_id")
                        ->getOneOrNullResult();
    }

    public function getUnassignedTasksByProjectId($project_id) {

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Task', 'ChildTask')
                ->from('AppBundle\Entity\Task', 'Task')
                ->leftJoin('Task.children', 'ChildTask')
                ->where('Task.sprint IS NULL')
                ->andWhere('Task.project = :id')
                ->setParameter('id', $project_id)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getStatTasksTimeByProjectId($project_id) {

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Task', 'User')
                ->from('AppBundle\Entity\Task', 'Task')
                ->leftJoin('Task.user', 'User')
                ->where('Task.project = :id')
                ->andWhere('Task.estimated_time is not null')
                ->andWhere('Task.spended_time is not null')
                ->setParameter('id', $project_id)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getStatTasksExpiredByProjectId($project_id) {

        $date = date('Y-m-d');

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Task.id as count_task , Sprint.dateEnd as sprint_id')
                ->from('AppBundle\Entity\Task', 'Task')
                ->leftJoin('Task.log', 'Log')
                ->leftJoin('Task.sprint', 'Sprint')
                ->where('Task.project = :id')
                ->andWhere('Log.date > Sprint.dateEnd')
                ->andWhere('Task.state != :state')
                ->orderBy('Log.id', 'DESC')
                ->setParameter('id', $project_id)
                ->setParameter('state', 'Finished')
                //->setParameter('now_date', $date)
                ->groupBy('Log.task')
                ->addGroupBy('Sprint.id')
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

}
