<?php

namespace App\Enum;

enum AttributeDimension: string {
    case VALUE = 'value';
    case LENGTH = 'length';

    // Exclusive to Period
    case DURATION = 'duration';
    case START_DATE = 'start_date';
    case END_DATE = 'end_date';
}