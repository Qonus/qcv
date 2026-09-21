<?php

namespace App\Repository;

use App\Entity\AccessRule;
use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\Position;
use App\Entity\PositionAttribute;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use TypeaheadRepositoryInterface;

/**
 * @extends ServiceEntityRepository<Attribute>
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        parent::__construct($registry, Attribute::class);
    }


    public function search(string $query): array {
        return $this->createQueryBuilder('a')
            ->andWhere("LOWER(a.name) LIKE LOWER(:q)")
            ->setParameter('q', '%'.$query.'%')
            ->orderBy('a.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function searchNewForUser(User $user, string $query): array {
        $subQuery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(AttributeValue::class, 'av')
            ->where('av.attribute = a')
            ->andWhere('av.candidate = :user');

        return $this->createQueryBuilder('a')
            ->andWhere("LOWER(a.name) LIKE LOWER(:q)")
            ->andWhere("NOT EXISTS ({$subQuery->getDQL()})")
            ->setParameter('q', '%'.$query.'%')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function searchNewForPosition(Position $position, string $query): array {
        $subQuery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(PositionAttribute::class, 'pa')
            ->where('pa.attribute = a')
            ->andWhere('pa.position = :position');

        return $this->createQueryBuilder('a')
            ->andWhere("LOWER(a.name) LIKE LOWER(:q)")
            ->andWhere("NOT EXISTS ({$subQuery->getDQL()})")
            ->setParameter('q', '%'.$query.'%')
            ->setParameter('position', $position)
            ->orderBy('a.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Attribute[] Returns an array of Attribute objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Attribute
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}