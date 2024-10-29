<?php

namespace App\Repository;

use App\Entity\OrderHistoryLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderHistoryLogs>
 */
class OrderHistoryLogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderHistoryLogs::class);
    }

    public function getPaginatedLogs(int $page, int $limit)
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('o')
            ->orderBy('o.changedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalLogCount(): int
    {
        return count($this->createQueryBuilder('o')
            ->select('o.id')
            ->getQuery()
            ->getResult());
    }

}
