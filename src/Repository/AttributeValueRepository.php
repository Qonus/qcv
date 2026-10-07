<?php

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Enum\AttributeDataType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeValue>
 */
class AttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeValue::class);
    }

    public function getNumericStats(Attribute $attribute): array
    {
        return $this->createQueryBuilder('a')
            ->select(
                'AVG(a.valueNumeric) AS avg',
                'MIN(a.valueNumeric) AS min',
                'MAX(a.valueNumeric) AS max'
            )
            ->where('a.attribute = :attribute')
            ->setParameter('attribute', $attribute)
            ->getQuery()
            ->getSingleResult();
    }

    public function getTopValues(Attribute $attribute, int $limit = 5): array
    {
        $field = match ($attribute->getDataType()) {
            AttributeDataType::STRING  => 'valueString',
            AttributeDataType::IMAGE   => 'valueImageUrl',
            AttributeDataType::TEXT    => 'valueText',
            AttributeDataType::BOOLEAN => 'valueBoolean',
            default                    => 'valueString',
        };
        $results = $this->createQueryBuilder('v')
            ->select(sprintf('v.%s as val', $field), 'COUNT(v.id) as count')
            ->where('v.attribute = :attribute')
            ->andWhere(sprintf('v.%s IS NOT NULL', $field))
            ->setParameter('attribute', $attribute)
            ->groupBy(sprintf('v.%s', $field))
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        $topValues = [];
        foreach ($results as $row) {
            $rawVal = $row['val'];
            $displayVal = match ($attribute->getDataType()) {
                AttributeDataType::BOOLEAN => $rawVal ? 'True' : 'False',
                AttributeDataType::SELECT  => $rawVal->getName(),
                default                    => (string) $rawVal,
            };
            $topValues[] = [
                'value' => $displayVal,
                'count' => (int) $row['count'],
            ];
        }
        return [
            // 'count'      => $totalCount,
            'top_values' => $topValues,
        ];
    }

    public function getTopOptionValues(Attribute $attribute, int $limit = 3) {
        $results = $this->createQueryBuilder('v')
            ->innerJoin('v.valueOption', 'opt')
            ->select('opt.value as val', 'COUNT(v.id) as count') 
            ->where('v.attribute = :attribute')
            ->setParameter('attribute', $attribute)
            ->groupBy('opt.id', 'opt.value')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        $topValues = array_map(fn($row) => [
            'value' => (string) $row['val'],
            'count' => (int) $row['count'],
        ], $results);
        return [
            'top_values' => $topValues,
        ];
    }

    public function findOneByUserAndName(User $user, string $attributeName, bool $isBuiltin = true): ?AttributeValue {
        return $this->createQueryBuilder('av')
            ->innerJoin('av.attribute', 'a')
            ->andWhere('av.candidate = :user')
            ->setParameter('user', $user)
            ->andWhere('a.name = :name')
            ->setParameter('name', $attributeName)
            ->andWhere('a.isBuiltin = :isBuiltin')
            ->setParameter('isBuiltin', $isBuiltin)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return array<string, object{
    //  *     id: int,
    //  *     name: string,
    //  *     type: AttributeDataType,
    //  *     value: mixed,
    //  *     category: object,
    //  *     description: ?string,
    //  *     version: int,
    //  *     options: mixed
    //  * }>
    //  */
    public function findByUser(User $user, bool $isBuiltin, ?string $query = ''): array {
        /** @var AttributeValue[] $results */
        return $this->createQueryBuilder('av')
            // Avoid n+1 queries by pre-fetching attributes, categories and options
            ->innerJoin('av.attribute', 'a')
            ->leftJoin('a.category', 'ac')
            ->leftJoin('av.valueOption', 'vo')
            ->leftJoin('a.options', 'opts')
            ->addSelect('a', 'ac', 'vo', 'opts')
            
            ->andWhere('LOWER(a.name) LIKE LOWER(:query)')
            // ->orWhere('LOWER(a.description) LIKE LOWER(:query)')
            ->setParameter('query', '%'.$query.'%')
            ->andWhere('a.isBuiltin = :isBuiltin')
            ->setParameter('isBuiltin', $isBuiltin)
            ->andWhere('av.candidate = :user')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
        
        // $output = [];

        // foreach ($results as $av) {
        //     $attribute = $av->getAttribute();
        //     $name = $attribute->getName();

        //     $output[$name] = (object) [
        //         'id' => $av->getId(),
        //         'name' => $name,
        //         'type' => $av->getAttribute()->getDataType(),
        //         'value' => $av->getValue(),
        //         'version' => $av->getVersion(),
        //         'category' => $attribute->getCategory(),
        //         'description' => $attribute->getDescription(),
        //         'options'     => $attribute->getOptions(),
        //     ];
        // }

        // return $output;
    }

    //    /**
    //     * @return AttributeValue[] Returns an array of AttributeValue objects
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

    //    public function findOneBySomeField($value): ?AttributeValue
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
