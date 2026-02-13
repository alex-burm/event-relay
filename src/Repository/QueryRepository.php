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

    public function findLatest(int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.rule', 'r')
            ->addSelect('r')
            ->orderBy('q.receivedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countToday(): int
    {
        $today = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.receivedAt >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('q')
            ->select('q.status, COUNT(q.id) as cnt')
            ->groupBy('q.status')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['cnt'];
        }

        return $result;
    }

    public function countByRuleForPeriod(string $period): array
    {
        $qb = $this->createQueryBuilder('q')
            ->select('r.name as rule_name, COUNT(q.id) as cnt')
            ->leftJoin('q.rule', 'r')
            ->groupBy('r.id, r.name')
            ->orderBy('cnt', 'DESC');

        if ($period === 'today') {
            $qb->where('q.receivedAt >= :start')
                ->setParameter('start', new \DateTimeImmutable('today'));
        } elseif ($period === 'week') {
            $qb->where('q.receivedAt >= :start')
                ->setParameter('start', new \DateTimeImmutable('-7 days midnight'));
        }

        return $qb->getQuery()->getResult();
    }

    public function countByDayLast7Days(): array
    {
        $since = new \DateTimeImmutable('-6 days midnight');

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DATE(received_at) as day, COUNT(*) as cnt
                FROM query
                WHERE received_at >= :since
                GROUP BY DATE(received_at)
                ORDER BY day ASC";

        $rows = $conn->executeQuery($sql, ['since' => $since->format('Y-m-d H:i:s')])->fetchAllAssociative();

        // Fill in missing days
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $result[$date] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['day']] = (int) $row['cnt'];
        }

        return $result;
    }

    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
