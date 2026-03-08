<?php

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\User;
use App\Filter\TodoFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Todo>
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Todo::class);
    }

    public function findByFilter(TodoFilter $filter, User $user): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', $filter->sort);

        $filters = [
            'search' => fn($qb) => $qb
                ->andWhere('t.title LIKE :search')
                ->setParameter('search', "%{$filter->search}%"),

            'status' => fn($qb) => $qb
                ->andWhere('t.status = :status')
                ->setParameter('status', $filter->status),
        ];

        foreach ($filters as $field => $apply) {
            if ($filter->$field !== null && $filter->$field !== '') {
                $apply($qb);
            }
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Todo[] Returns an array of Todo objects
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

    //    public function findOneBySomeField($value): ?Todo
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
