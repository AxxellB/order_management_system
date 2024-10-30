<?php

namespace App\Repository;

use App\Entity\ProductStockHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductStockHistory>
 */
class ProductStockHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductStockHistory::class);
    }

    public function add(ProductStockHistory $productStockHistory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($productStockHistory);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
