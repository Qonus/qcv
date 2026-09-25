<?php

namespace App\Repository;

use App\Entity\CV;
use App\Entity\Position;
use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findByTag(?string $tag = '') {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.tags', 't')
            ->andWhere('LOWER(t.name) LIKE LOWER(:tag)')
            ->setParameter('tag', '%'.$tag.'%')
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $candidate, ?string $query = '') {
        // WARNING: USING ORWHERE IS DANGEROUS, USE CAREFULLY
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.description) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->andWhere('p.candidate = :candidate')
            ->setParameter('candidate', $candidate)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCV(CV $cv,) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.candidate = :candidate')
            ->setParameter('candidate', $cv->getCandidate())
            ->innerJoin('p.tags', 'pt')
            ->addSelect('COUNT(pt.id) AS HIDDEN score')
            ->innerJoin('pt.positions', 'pos', 'WITH', 'pos = :position')
            ->setParameter('position', $cv->getPosition())
            ->groupBy('p.id')
            ->having('COUNT(pt.id) > 0')
            ->orderBy('score', 'DESC')
            ->setMaxResults($cv->getPosition()->getMaxProjects())
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Project[] Returns an array of Project objects
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

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
