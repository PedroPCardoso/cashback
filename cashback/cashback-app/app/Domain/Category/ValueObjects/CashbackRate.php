<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObjects;

use InvalidArgumentException;

readonly class CashbackRate
{
    public function __construct(
        private float $rate
    ) {
        if ($rate < 0 || $rate > 100) {
            throw new InvalidArgumentException('Cashback rate must be between 0 and 100');
        }
    }

    public function value(): float
    {
        return $this->rate;
    }
}
