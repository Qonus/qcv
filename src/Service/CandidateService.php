<?php
namespace App\Service;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandidateService
{
    public const BUILTIN_FIRST_NAME = 'First Name';
    public const BUILTIN_LAST_NAME  = 'Last Name';
    public const BUILTIN_LOCATION   = 'Location';
    public const BUILTIN_PHOTO      = 'Personal Photo';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AttributeRepository $attributeRepo,
        private readonly AttributeValueRepository $valueRepo
    ) {}

    /**
     * @param array<string, string> $builtinValues Key-value pairs ['First Name' => 'John', ...]
     */
    public function saveBuiltinValues(User $user, array $builtinValues): void
    {
        // TODO: Add Optimistic Locking check
        foreach ($builtinValues as $attributeName => $value) {
            $this->updateOrCreateBuiltin($user, $attributeName, (string) $value);
        }

        $this->em->flush();
    }

    private function updateOrCreateBuiltin(User $user, string $attributeName, string $newValue): void
    {
        $attributeValue = $this->valueRepo->findOneByUserAndName($user, $attributeName);

        if (!$attributeValue) {
            $attribute = $this->attributeRepo->findOneBy(['name' => $attributeName, 'isBuiltin' => true]);
            if (!$attribute) {
                return;
            }

            $attributeValue = new AttributeValue();
            $attributeValue->setCandidate($user);
            $attributeValue->setAttribute($attribute);
            $this->em->persist($attributeValue);
        }

        $attributeValue->setValueString($newValue);
    }
}