<?php

declare(strict_types=1);

namespace App\Domain\Cashback\Services;

use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;

readonly class CalculationResult
{
    public function __construct(
        private Money $cashback,
        private CategoryStatus $newStatus
    ) {}

    public function cashback(): Money
    {
        return $this->cashback;
    }

    public function newStatus(): CategoryStatus
    {
        return $this->newStatus;
    }
}
