<?php

namespace App\Repository;

use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
    
    public function search(?string $query, ?string $tag = '') {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.company) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.level) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%');
        if ($tag != '') {
            $qb = $qb->innerJoin('p.tags', 't')
            ->andWhere('LOWER(t.name) LIKE LOWER(:tag)')
            ->setParameter('tag', '%'.$tag.'%');
        }
        return $qb->getQuery()->getResult();
    }

    public function latest(?int $limit = null) {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function popular(?int $limit = null) {
        return $this->createQueryBuilder('p')
            ->select('p', 'COUNT(c.id) AS HIDDEN cvCount')
            ->leftJoin('p.cvs', 'c', Join::WITH, 'c.isPublic = true')
            ->groupBy('p.id')
            ->orderBy('cvCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
