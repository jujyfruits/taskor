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

}
