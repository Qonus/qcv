<?php

namespace App\Repository;

use App\Entity\AttributeValue;
use App\Entity\CV;
use App\Entity\Position;
use App\Entity\PositionAttribute;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CV>
 */
class CVRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CV::class);
    }

    public function findPositionAttributesWithCandidateValues(Position $position, User $candidate): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('pa', 'a', 'av')
            ->from(PositionAttribute::class, 'pa')
            ->join('pa.attribute', 'a')
            ->leftJoin(
                AttributeValue::class,
                'av',
                Join::WITH,
                'av.attribute = a AND av.candidate = :candidate'
            )
            ->andWhere('pa.position = :position')
            ->setParameter('position', $position)
            ->setParameter('candidate', $candidate)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return CV[] Returns an array of CV objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CV
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
