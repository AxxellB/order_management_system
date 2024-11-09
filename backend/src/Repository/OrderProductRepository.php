<?php

namespace App\Repository;

use App\Entity\OrderProduct;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderProduct>
 */
class OrderProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProduct::class);
    }

    public function totalProductsSold(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        $result = $this->createQueryBuilder('op')
            ->select('SUM(op.quantity)')
            ->join('op.orderEntity', 'o')
            ->where('o.orderDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)($result ?? 0);
    }

    public function totalProductsSoldChart(DateTime $startDate, DateTime $endDate): array
    {
        $query = $this->createQueryBuilder('op')
            ->select('o.orderDate', 'SUM(op.quantity) as totalSold')
            ->leftJoin('op.orderEntity', 'o')
            ->where('o.orderDate BETWEEN :startDate AND :endDate')
            ->groupBy('o.orderDate')
            ->orderBy('o.orderDate', 'ASC')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();

        $result = $query->getResult();

        $salesData = array_map(function ($data) {
            return [
                'date' => $data['orderDate']->format('Y-m-d'),
                'salesAmount' => (int)$data['totalSold'],
            ];
        }, $result);

        return $salesData;
    }

    public function productSales(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('op')
            ->select('p.name, SUM(op.quantity) as totalSold')
            ->join('op.ProductEntity', 'p')
            ->join('op.orderEntity', 'o')
            ->where('o.orderDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('p.id')
            ->orderBy('totalSold', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function topSoldProducts(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('op')
            ->select('p.name', 'SUM(op.quantity) as totalSold')
            ->join('op.ProductEntity', 'p')
            ->join('op.orderEntity', 'o')
            ->where('o.orderDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('p.id')
            ->orderBy('totalSold', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
