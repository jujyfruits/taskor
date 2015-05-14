<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function findUserProjects($user) {

        return 'here_are_all_your_projects';
    }

    public function findNotParticipantsOfProject($id) {
        /*

          $this->project != $id;

          $qb = $this->createQueryBuilder('u');
          $qb->where('u.project !=' . $id);

          return $qb->getQuery()
          ->getResult();
         */

        $result = $this->getEntityManager()->createQuery("SELECT p FROM AppBundle\Entity\User as p
        JOIN p.project s WHERE s.id <>". $id);

        return $result->getResult();
    }

}
