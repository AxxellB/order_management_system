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
        $qb = $this->createQueryBuilder('p');

        if (isset($criteria['category'])) {
            $qb->join('p.categories', 'c')
            ->andWhere('c.id = :category')
            ->setParameter('category', $criteria['category']);
        }

        if (isset($criteria['minPrice'])) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $criteria['minPrice']);
        }

        if (isset($criteria['maxPrice'])) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        if (isset($criteria['minStock'])) {
            $qb->andWhere('p.stockQuantity >= :minStock')
                ->setParameter('minStock', $criteria['minStock']);
        }

        if (isset($criteria['maxStock'])) {
            $qb->andWhere('p.stockQuantity <= :maxStock')
                ->setParameter('maxStock', $criteria['maxStock']);
        }

        if (isset($criteria['deleted'])) {
            if ($criteria['deleted'] === true) {
                $qb->andWhere('p.deletedAt IS NOT NULL');
            } else {
                $qb->andWhere('p.deletedAt IS NULL');
            }
        } else {
            $qb->andWhere('p.deletedAt IS NULL');
        }

        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy('p.' . $field, $direction);
        }

        return $qb->getQuery()->getResult();
    }







}
