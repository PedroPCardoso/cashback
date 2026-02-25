<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObjects;

enum CategoryStatus: string
{
    case WITHIN_LIMIT = 'within_limit';
    case EXCEEDED = 'exceeded';
}
