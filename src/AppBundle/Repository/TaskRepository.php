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

}
