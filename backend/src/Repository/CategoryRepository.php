<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findRandomCategories(int $limit): array
    {
        $categories = $this->createQueryBuilder('c')
            ->andWhere('c.deletedAt IS NULL')
            ->getQuery()
            ->getResult();

        shuffle($categories);

        return array_slice($categories, 0, $limit);
    }

    public function fondAllNonDeletedCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

}
