<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function countOrdersByStatusAndSearch($status, $search)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)');

        if ($status === 'active') {
            $queryBuilder->where('o.deletedAt IS NULL');
        } elseif ($status === 'deleted') {
            $queryBuilder->where('o.deletedAt IS NOT NULL');
        }

        if ($search) {
            $queryBuilder
                ->leftJoin('o.users', 'u')
                ->andWhere('u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findOrdersByStatusAndSearch($status, $search, $page, $itemsPerPage)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->orderBy('o.orderDate', 'DESC');

        if ($status === 'active') {
            $queryBuilder->where('o.deletedAt IS NULL');
        } elseif ($status === 'deleted') {
            $queryBuilder->where('o.deletedAt IS NOT NULL');
        }

        if ($search) {
            $queryBuilder
                ->leftJoin('o.users', 'u')
                ->andWhere('u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function totalRevenue(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.orderDate AS orderDate', 'SUM(o.totalAmount) AS revenue')
            ->where('o.orderDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('orderDate')
            ->orderBy('orderDate', 'ASC')
            ->getQuery();

        return $qb->getResult();
    }
}
