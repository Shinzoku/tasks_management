<?php

namespace App\Repository;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

/**
 * @extends ServiceEntityRepository<TaskList>
 */
class TaskListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    //    /**
    //     * @return TaskList[] Returns an array of TaskList objects
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

    //    public function findOneBySomeField($value): ?TaskList
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findPaginatedTaskLists(int $page, int $limit, ?string $sortField = null, ?string $sortOrder = null): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->join('t.user', 'u'); // Join with the User entity

        // Apply sorting criteria if provided
        if ($sortField) {
            $sortOrder = $sortOrder === 'desc' ? 'DESC' : 'ASC';

            // Check if sorting by user field
            if ($sortField === 'userFullName') {
                // Sort by concatenation of firstname and lastname
            $queryBuilder->addOrderBy("CONCAT(u.firstname, ' ', u.lastname)", $sortOrder);
            } else {
                // Sort by task fields
                $queryBuilder->orderBy('t.' . $sortField, $sortOrder);
            }

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
