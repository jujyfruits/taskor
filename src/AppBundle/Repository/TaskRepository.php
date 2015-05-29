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

}
