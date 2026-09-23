<?php

namespace App\Service;

use App\Entity\AccessRule;
use App\Entity\Attribute;
use App\Entity\AttributeOption;
use App\Entity\Position;
use App\Enum\AttributeDataType;
use App\Enum\AttributeDimension;
use App\Enum\FilterValueType;
use App\Enum\MatchType;
use App\Enum\Operation;
use App\Repository\AttributeOptionRepository;

class AccessRuleService {
    public function __construct(private AttributeOptionRepository $attributeOptionRepository) {}

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
}