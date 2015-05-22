<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SprintRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SprintRepository extends EntityRepository {

    public function getProjectSprintByNumber($project_id, $sprint_number) {
        return $this->getEntityManager()
                        ->createQuery("select s from AppBundle\Entity\Sprint s where "
                                . "s.project = $project_id and s.number = $sprint_number")
                        ->getOneOrNullResult();
    }

}
