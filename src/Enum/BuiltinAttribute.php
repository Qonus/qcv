<?php

namespace App\Enum;

enum BuiltinAttribute: string
{
    case FIRST_NAME = 'First Name';
    case LAST_NAME = 'Last Name';
    case IMAGE_URL = 'Personal Photo';
    case LOCATION = 'Location';
}