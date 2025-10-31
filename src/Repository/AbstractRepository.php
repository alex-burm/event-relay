<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, $this->getMetaClass());
    }

    abstract protected function getMetaClass(): string;

    protected function prepare(string $sql): Statement
    {
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        //$stm->bindValue(':spaceId', $this->workspaceContext->getCurrentWorkspace()->getId(), ParameterType::INTEGER);
        return $stm;
    }
}
