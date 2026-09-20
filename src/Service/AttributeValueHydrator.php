<?php

namespace App\Service;

use App\Entity\AttributeValue;
use App\Enum\AttributeDataType;
use App\Repository\AttributeOptionRepository;
class AttributeValueHydrator
{
    public function __construct(
        private AttributeOptionRepository $attributeOptionRepository
    ) {}

    public function getStringValue(AttributeValue $attributeValue): mixed {
        $dataType = $attributeValue->getAttribute()?->getDataType();
        $value = $attributeValue->getValue();
        return match ($dataType) {
            AttributeDataType::BOOLEAN => $value ? "1" : "",
            AttributeDataType::STRING  => $value,
            AttributeDataType::TEXT    => $value,
            AttributeDataType::DATE    => $value?->format('Y-m-d'),
            AttributeDataType::PERIOD  => ['start'=>$value['start']?->format('Y-m-d'),'end'=>$value['end']?->format('Y-m-d')],
            AttributeDataType::IMAGE   => $value,
            AttributeDataType::NUMERIC => $value,
            AttributeDataType::SELECT  => $value !== null ? $value->getId() : null,
            default => null,
        };
    }

    public function setValueFromString(AttributeValue $attributeValue, mixed $value): void
    {
        $dataType = $attributeValue->getAttribute()?->getDataType();
        $attributeValue->setValue(match ($dataType) {
            AttributeDataType::BOOLEAN => $value === "1",
            AttributeDataType::STRING  => $value,
            AttributeDataType::TEXT    => $value,
            AttributeDataType::DATE    => new \DateTime($value),
            AttributeDataType::PERIOD  => [
                'start'=>$value['start'] ? new \DateTime($value['start']): null,
                'end'=>$value['end'] ? new \DateTime($value['end']) : null,],
            AttributeDataType::IMAGE   => $value,
            AttributeDataType::NUMERIC => $value,
            AttributeDataType::SELECT  => $value !== null ? $this->attributeOptionRepository->find((int) $value) : null,
            default => null,
        });
    }
}