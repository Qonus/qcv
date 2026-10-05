<?php

namespace App\Service;

use App\Entity\Position;

class PositionAggregator {
    public function __construct() {}
    public function aggregate(Position $position) {
        // TODO: Aggregate the Position
        return [
            'id' => $position->getId(),
            'title' => $position->getName(),
            'cv_count' => $position->getCvs()->count(),
            // 'attributes' => $
        ];
    }
}