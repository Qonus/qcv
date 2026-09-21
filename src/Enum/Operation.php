<?php

namespace App\Enum;

enum Operation: string {
    case EQUALS = 'equals';
    case NOT_EQUALS = 'not_equals';
    case GREATER_THAN = 'greater_than';
    case LESS_THAN = 'less_than';

    // TODO: Later
    // case GREATER_EQ_THAN = 'greater_eq_than';
    // case LESS_EQ_THAN = 'less_eq_than';
    // case CONTAINS = 'contains';
    // case STARTS_WITH = 'starts_with';
    // case CONTAINS_EXACT = 'contains_exact';
}