<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findById(int $id): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllNonDeleted(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->andWhere('c.deletedAt IS NULL')
            ->getQuery();

        $categories = $query->getArrayResult();

        return [
            'categories' => $categories,
        ];
    }



    public function findAllNonDeletedCategoriesWithPagination(int $offset, int $limit): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.id, c.name, COUNT(p.id) as productCount')
            ->leftJoin('c.products', 'p')
            ->andWhere('c.deletedAt IS NULL')
            ->groupBy('c.id')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, true);
        $categories = $paginator->getQuery()->getArrayResult();

        $countQuery = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.deletedAt IS NULL')
            ->getQuery();

        $totalCount = (int) $countQuery->getSingleScalarResult();

        return [
            'categories' => $categories,
            'totalCount' => $totalCount,
        ];
    }



}
