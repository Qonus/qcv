<?php

namespace App\Repository;

use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Position>
 */
class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }
    
    public function search(?string $query) {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.company) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.level) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Position[] Returns an array of Position objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Position
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
