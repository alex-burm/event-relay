<?php

namespace App\Repository;

use App\Entity\Query;
use Doctrine\ORM\EntityRepository;

class QueryRepository extends EntityRepository
{
    protected function getMetaClass(): string
    {
        return Query::class;
    }

}
