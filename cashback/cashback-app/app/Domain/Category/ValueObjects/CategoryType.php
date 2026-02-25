<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObjects;

enum CategoryType: string
{
    case DEFAULT = 'default';
    case CUSTOM = 'custom';
}
