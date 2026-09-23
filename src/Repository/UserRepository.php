<?php

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function createUser(User $user): void {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        // Add builtin attributes.
        $builtinAttributes = $this->getEntityManager()->getRepository(Attribute::class)->findBy(['isBuiltin' => true]);
        foreach ($builtinAttributes as $builtin) {
            $av = new AttributeValue();
            $av->setCandidate($user);
            $av->setAttribute($builtin);
            
            $this->getEntityManager()->persist($av);
        }
        $this->getEntityManager()->flush();
    }

    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere("CONCAT(u.roles, '') LIKE :role")
            ->setParameter('role', '%"'.$role.'"%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByEmail(string $email, string $role = ''): ?User {
        $qb = $this->createQueryBuilder('u')
            ->andWhere("u.email = :email")
            ->setParameter("email", $email);
        if ($role != '') {
            $qb = $qb->andWhere("CONCAT(u.roles, '') LIKE :role")
            ->setParameter('role', '%"'.$role.'"%');
        }
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function updateBlockByIds(array $ids, bool $block): int
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->set('u.isBlocked', ':block')
            ->where('u.id IN (:ids)')
            ->setParameter('block', $block)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
    public function deleteByIds(array $ids, bool $onlyUnverified = false): int
    {
        $qb = $this->createQueryBuilder('u')
            ->delete()
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);
        if ($onlyUnverified) {
            $qb->andWhere('u.isVerified = :unverifiedStatus')
            ->setParameter('unverifiedStatus', false);
        }

        return $qb->getQuery()->execute();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
