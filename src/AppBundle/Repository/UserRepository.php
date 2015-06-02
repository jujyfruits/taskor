<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function findNotParticipantsOfProject($id) {

        $result = $this->getEntityManager()
                ->createQuery("SELECT usr FROM AppBundle\Entity\User as usr WHERE usr.id NOT IN
          (SELECT p FROM AppBundle\Entity\User as p
          LEFT JOIN p.project s WHERE s.id =" . $id . ')');

        return $result->getResult();
    }

    public function getUsersBusynessByProjectId($project_id) {

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('User.username','Sprint.id AS sprint_id',
                        'COUNT(Task.estimated_time) AS  count_task','SUM(Task.estimated_time) AS  estimtime')
                ->from('AppBundle\Entity\User', 'User')
                ->leftJoin('User.task', 'Task')
                ->leftJoin('Task.sprint', 'Sprint')
                ->where('Task.project = :id')
                ->andWhere('Task.state = :state')
                ->groupBy('Task.sprint')
                ->setParameter('id', $project_id)
                ->setParameter('state', 'Started')
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }


}
