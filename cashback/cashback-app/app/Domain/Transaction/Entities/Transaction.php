<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Entities;

use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;

class Transaction
{
    public function __construct(
        private string $id,
        private string $description,
        private Money $value,
        private TransactionDate $date,
        private ?string $categoryId
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function value(): Money
    {
        return $this->value;
    }

    public function date(): TransactionDate
    {
        return $this->date;
    }

    public function categoryId(): ?string
    {
        return $this->categoryId;
    }

    public function changeCategory(string $newCategoryId): void
    {
        $this->categoryId = $newCategoryId;
    }

    public function changeValue(Money $newValue): void
    {
        $this->value = $newValue;
    }

    public function changeDate(TransactionDate $newDate): void
    {
        $this->date = $newDate;
    }
}
