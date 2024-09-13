<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        /**
            * @return Task[] Returns an array of Task objects
            */
        public function findById($user): array
        {
            return $this->createQueryBuilder('t')
                ->andWhere('t.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult()
            ;
        }

        public function findPaginatedTasks(int $page, int $limit, ?string $sortField = null, ?string $sortOrder = null): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('t');

        // Apply sorting criteria if provided
        if ($sortField) {
            $sortOrder = $sortOrder === 'desc' ? 'DESC' : 'ASC';
            $queryBuilder->orderBy('t.' . $sortField, $sortOrder);
        } else {
            // Default sorting (for example, by ID)
            $queryBuilder->orderBy('t.id', 'ASC');
        }

        // Create the adapter for Pagerfanta
        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}
