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

    public function getUserProjectsIds($id) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Project.id')
                ->from('AppBundle\Entity\Project', 'Project')
                ->leftJoin('Project.user', 'User')
                ->where('User.id = :id')
                ->setParameter('id', $id)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    
    public function getUserProjects($id) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Project')
                ->from('AppBundle\Entity\Project', 'Project')
                ->leftJoin('Project.user', 'User')
                ->where('User.id = :id')
                ->andWhere('Project.archived = FALSE')
                ->setParameter('id', $id)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    public function getArchivedUserProjects($id) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
                ->select('Project')
                ->from('AppBundle\Entity\Project', 'Project')
                ->leftJoin('Project.user', 'User')
                ->where('User.id = :id')
                ->andWhere('Project.archived != FALSE')
                ->setParameter('id', $id)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

}
