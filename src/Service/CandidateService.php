<?php
namespace App\Service;

use App\Entity\AccessRule;
use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Enum\BuiltinAttribute;
use App\Enum\Operation;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandidateService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AttributeRepository $attributeRepo,
        private readonly UserRepository $userRepository,
        private readonly AttributeValueRepository $attributeValueRepository
    ) {}

    public function getCandidateAttributes(User $candidate, bool $returnBuiltinValues = true, bool $returnOtherAttributes = true) {
        $attributeValues = [];
        if ($returnOtherAttributes) {
            $attributeValues = $this->attributeValueRepository->findByUser($candidate, false);
        }
        $builtinValues = [];
        if ($returnBuiltinValues) {
            $valuesByName = [];
            foreach ($this->attributeValueRepository->findByUser($candidate, true) as $value) {
                $valuesByName[$value->getAttribute()->getName()] = $value;
            }
            $builtinValues = [
                'firstName' => ($valuesByName[BuiltinAttribute::FIRST_NAME->value] ?? null)?->getValue() ?? '',
                'lastName'  => ($valuesByName[BuiltinAttribute::LAST_NAME->value]  ?? null)?->getValue() ?? '',
                'location'  => ($valuesByName[BuiltinAttribute::LOCATION->value]   ?? null)?->getValue() ?? '',
                'image'     => ($valuesByName[BuiltinAttribute::IMAGE_URL->value]  ?? null)?->getValue() ?? '',
            ];
        }
        return [
            ...$builtinValues,
            'attributeValues' => $attributeValues
        ];
    }

    public function builtinValuesExist(User $candidate): bool {
        foreach ($this->attributeValueRepository->findByUser($candidate, true) as $value) {
            if (!$value->getValueExists()) return false;
        }
        return true;
    }

    public function accessRule(AccessRule $accessRule, User $candidate): bool {
        // TODO: rename the func, finish it and use in in PositionVoter->canView()
        return true;
        // $accessRule->getAttribute();
        // $filterValue = $accessRule->getFi
        // return match ($accessRule->getOperation()) {
        //     Operation::EQUALS => ,
        //     default => false
        // };
    }

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

    private function updateOrCreateBuiltin(User $user, string $attributeName, mixed $newValue): void
    {
        $attributeValue = $this->attributeValueRepository->findOneByUserAndName($user, $attributeName);

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

        $attributeValue->setValue($newValue);
    }
}