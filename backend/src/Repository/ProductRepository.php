<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllNonDeletedProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findAllDeletedProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.deletedAt IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCriteriaAndOrder(array $criteria, array $orderBy): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $queryBuilder->andWhere('p.deletedAt IS NULL');

        if (!empty($criteria['category'])) {
            $queryBuilder->leftJoin('p.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $criteria['category']);
        }

        if (!empty($criteria['minPrice']) && is_numeric($criteria['minPrice'])) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $criteria['minPrice']);
        }

        if (!empty($criteria['maxPrice']) && is_numeric($criteria['maxPrice'])) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        if (!empty($criteria['minStock']) && is_numeric($criteria['minStock'])) {
            $queryBuilder->andWhere('p.stockQuantity >= :minStock')
                ->setParameter('minStock', $criteria['minStock']);
        }

        if (!empty($criteria['maxStock']) && is_numeric($criteria['maxStock'])) {
            $queryBuilder->andWhere('p.stockQuantity <= :maxStock')
                ->setParameter('maxStock', $criteria['maxStock']);
        }

        foreach ($orderBy as $field => $direction) {
            if (in_array($field, ['name', 'price', 'stockQuantity'])) {
                $queryBuilder->addOrderBy('p.' . $field, $direction);
            }
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }


}
