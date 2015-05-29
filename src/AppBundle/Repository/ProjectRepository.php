<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\DateTime;

class ProjectRepository extends EntityRepository {

    public function getProjectSprintsList($project_id) {
        $result = $this->getEntityManager()
                ->createQuery("SELECT spr FROM AppBundle\Entity\Sprint as spr WHERE spr.id IN
          (SELECT s FROM AppBundle\Entity\Sprint as s
          LEFT JOIN s.project p WHERE p.id=$project_id)");

        return $result->getResult();
    }

}
