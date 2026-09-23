<?php

namespace App\Service;

use App\Entity\AccessRule;
use App\Entity\Attribute;
use App\Entity\AttributeOption;
use App\Entity\AttributeValue;
use App\Entity\Position;
use App\Entity\User;
use App\Enum\AttributeDataType;
use App\Enum\AttributeDimension;
use App\Enum\FilterValueType;
use App\Enum\MatchType;
use App\Enum\Operation;
use App\Repository\AttributeOptionRepository;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use DateTime;

class AccessRuleService {
    public function __construct(
        private AttributeOptionRepository $attributeOptionRepository,
        private AttributeValueRepository $attributeValueRepository,
    ) {}

    public function createAccessRule(
        Position $position,
        MatchType|string $matchType,
        Attribute $attribute,
        AttributeDimension|string $attributeDimension,
        Operation|string $operation,
        string $value
        ): AccessRule {
        $newAccessRule = new AccessRule();
        $newAccessRule->setPosition($position);
        $newAccessRule->setMatchType($matchType);
        $newAccessRule->setAttribute($attribute);
        $newAccessRule->setOperation($operation);
        $newAccessRule->setMatchType($matchType);
        $newAccessRule->setAttributeDimension($attributeDimension);
        $newAccessRule->setFilterValueType($this->deriveFilterValueType($newAccessRule));
        $newAccessRule->setValue(match ($newAccessRule->getFilterValueType()) {
            FilterValueType::OPTION => $this->attributeOptionRepository->find($value),
            FilterValueType::BOOLEAN => $value == 'true',
            default => $value,
        });
        return $newAccessRule;
    }

    public function getStringAccessRule(AccessRule $accessRule): array {
        return [
            'id' => $accessRule->getId(),
            'matchType' => $accessRule->getMatchType()->value,
            'attributeId' => $accessRule->getAttribute()?->getId(),
            'attributeDimension' => $accessRule->getAttributeDimension()->value,
            'operation' => $accessRule->getOperation()->value,
            'filterValue' => match($accessRule->getFilterValueType()) {
                FilterValueType::BOOLEAN => $accessRule->getValue() ? 'true' : 'false',
                FilterValueType::STRING => $accessRule->getValue(),
                FilterValueType::DURATION => $accessRule->getValue(),
                FilterValueType::NUMBER => $accessRule->getValue(),
                FilterValueType::DATE => $accessRule->getValue()->format('Y-m-d'),
            },
        ];
    }

    public function deriveFilterValueType(AccessRule $accessRule): ?FilterValueType {
        return match ($accessRule->getAttributeDimension()) {
            AttributeDimension::DURATION => FilterValueType::DURATION,
            AttributeDimension::END_DATE, AttributeDimension::START_DATE => FilterValueType::DATE,
            AttributeDimension::LENGTH => FilterValueType::NUMBER,
            AttributeDimension::VALUE => match ($accessRule->getAttribute()?->getDataType()) {
                AttributeDataType::DATE => FilterValueType::DATE,
                AttributeDataType::BOOLEAN => FilterValueType::BOOLEAN,
                AttributeDataType::NUMERIC => FilterValueType::NUMBER,
                AttributeDataType::TEXT, AttributeDataType::STRING, AttributeDataType::IMAGE => FilterValueType::STRING,
                AttributeDataType::SELECT => FilterValueType::OPTION,
                default => null,
            },
            default => null,
        };
    }

    public function checkAccessRuleForUser(AccessRule $accessRule, ?User $candidate): bool {
        if (!$candidate) return false;
        /**
         * @var AttributeValue
         */
        $attributeValue = $this->attributeValueRepository->findOneBy(['candidate' => $candidate, 'attribute' => $accessRule->getAttribute()]);
        if (!$attributeValue) return false;
        $userValue = $attributeValue->getValue();
        $filterValue = $accessRule->getValue();
        return match ($accessRule->getAttributeDimension()) {
            AttributeDimension::VALUE,
            AttributeDimension::LENGTH,
            AttributeDimension::START_DATE,
            AttributeDimension::END_DATE,
            => $this->computeOperation($userValue, $accessRule->getOperation(), $filterValue),
            AttributeDimension::DURATION => $this->computeOperation(
                $this->getAbsoluteDaysBetween($userValue['start'], $userValue['end']),
                $accessRule->getOperation(),
                $filterValue),
            default => false
        };
    }

    public function computeOperation(mixed $a, Operation $operation, mixed $b): bool {
        return match($operation) {
            Operation::EQUALS => $a == $b,
            Operation::NOT_EQUALS => $a != $b,
            Operation::LESS_THAN => $a < $b,
            Operation::GREATER_THAN => $a > $b,
        };
    }

    private function getAbsoluteDaysBetween(string|DateTime $date1, string|DateTime $date2): int 
    {
        $d1 = $date1 instanceof DateTime ? $date1 : new DateTime($date1);
        $d2 = $date2 instanceof DateTime ? $date2 : new DateTime($date2);
        return $d1->diff($d2)->days;
    }
}