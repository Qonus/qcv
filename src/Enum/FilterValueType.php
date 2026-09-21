<?php

namespace App\Enum;

enum FilterValueType: string {
    case NUMBER = 'number';
    case STRING = 'string';
    case DATE = 'date';
    case BOOLEAN = 'boolean';
    case OPTION = 'option';
    case DURATION = 'duration';
}