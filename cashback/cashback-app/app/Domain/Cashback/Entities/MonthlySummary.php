<?php

declare(strict_types=1);

namespace App\Domain\Cashback\Entities;

use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;

class MonthlySummary
{
    public function __construct(
        private string $categoryId,
        private int $year,
        private int $month,
        private Money $totalSpent,
        private Money $cashbackEarned,
        private CategoryStatus $status
    ) {}

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function month(): int
    {
        return $this->month;
    }

    public function totalSpent(): Money
    {
        return $this->totalSpent;
    }

    public function cashbackEarned(): Money
    {
        return $this->cashbackEarned;
    }

    public function status(): CategoryStatus
    {
        return $this->status;
    }

    public function applyTransaction(
        Money $value,
        Money $cashback,
        CategoryStatus $newStatus
    ): void {
        $this->totalSpent = $this->totalSpent->add($value);
        $this->cashbackEarned = $this->cashbackEarned->add($cashback);
        $this->status = $newStatus;
    }

    public function reverseTransaction(
        Money $value,
        Money $cashback,
        CategoryStatus $newStatus
    ): void {
        $this->totalSpent = $this->totalSpent->subtract($value);
        $this->cashbackEarned = $this->cashbackEarned->subtract($cashback);
        $this->status = $newStatus;
    }
}
