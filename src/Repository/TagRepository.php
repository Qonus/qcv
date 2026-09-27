<?php

namespace App\Repository;

use App\Entity\Position;
use App\Entity\Project;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use TypeaheadRepositoryInterface;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function search(string $query, array $exclude = null): array {
        $qb = $this->createQueryBuilder('t')
        ->andWhere("LOWER(t.name) LIKE LOWER(:q)")
        ->setParameter("q", '%' . $query . '%');
        if ($exclude) {
            $qb->andWhere('t.name NOT IN (:exclude)')
            ->setParameter('exclude', $exclude);
        }
        return $qb->orderBy('t.name', 'ASC')
        ->setMaxResults(8)
        ->getQuery()
        ->getResult();
    }

    public function popular(
        ?Project $project = null,
        ?Position $position = null,
        ?int $limit = null) {
        $qb = $this->createQueryBuilder('t')
            ->select('t', 'COUNT(DISTINCT position.id)+COUNT(DISTINCT project.id) AS HIDDEN usage')
            ->leftJoin('t.positions', 'position')
            ->leftJoin('t.projects', 'project')
            ->groupBy('t.id')
            ->orderBy('usage', 'DESC')
            ->setMaxResults($limit);
        if ($position) {
            $qb->andWhere('position = :position')
            ->setParameter('position', $position);
        }
        if ($project) {
            $qb->andWhere('project = :project')
            ->setParameter('project', $project);
        }
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Tag[] Returns an array of Tag objects
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

    //    public function findOneBySomeField($value): ?Tag
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
