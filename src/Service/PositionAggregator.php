<?php

namespace App\Service;

use App\Entity\Position;
use App\Enum\AttributeDataType;
use App\Repository\AttributeValueRepository;

class PositionAggregator {
    public function __construct(
        private AttributeValueRepository $attributeValueRepository,
    ) {}
    public function aggregate(Position $position) {
        $attributes = [];
        foreach ($position->getAttributes() as $a) {
            $item = [
                'name' => $a->getName(),
                'type' => $a->getDataType()->value,
                'count' => $this->attributeValueRepository->count(['attribute' => $a]),
            ];
            $item += match ($a->getDataType()) {
                AttributeDataType::NUMERIC => 
                    $this->attributeValueRepository->getNumericStats($a),
                AttributeDataType::SELECT => 
                    $this->attributeValueRepository->getTopOptionValues($a, limit: 3),
                default =>
                    $this->attributeValueRepository->getTopValues($a, limit: 3)
            };
            $attributes[] = $item;
        }
        return [
            'id' => $position->getId(),
            'title' => $position->getName(),
            'cv_count' => $position->getCvs()->count(),
            'attributes' => $attributes
        ];
    }
}