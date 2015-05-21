<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository {

    public function getSprintByNumber($project_id,$sprint_number) {
        
          $result = $this->getEntityManager()
          ->createQuery("SELECT spr FROM AppBundle\Entity\Sprint as spr WHERE spr.number = " .$sprint_number. " AND spr.id IN
          (SELECT sp FROM AppBundle\Entity\Sprint as sp
          LEFT JOIN sp.project s WHERE s.id =" . $project_id . ')');
          return $result->getResult();
         
        /*
        return $this->createQueryBuilder('p')
                        ->leftJoin('p.sprint', 'c')
                        ->where('c.number = ?1')
                        ->setParameter(1, $number)
                        ->getQuery()
                        ->getOneOrNullResult();
         */
    }

}
