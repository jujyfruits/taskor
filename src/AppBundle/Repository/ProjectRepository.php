<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository {

    public function findOfThisUser() {
        return 'vasilii_sosal_hui';
    }

}
