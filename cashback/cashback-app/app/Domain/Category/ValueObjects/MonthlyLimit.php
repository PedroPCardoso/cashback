<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObjects;

use App\Domain\Transaction\ValueObjects\Money;

readonly class MonthlyLimit
{
    public function __construct(
        private ?Money $money = null
    ) {}

    public static function unlimited(): self
    {
        return new self(null);
    }

    public static function fixed(Money $money): self
    {
        return new self($money);
    }

    public function isUnlimited(): bool
    {
        return $this->money === null;
    }

    public function money(): ?Money
    {
        return $this->money;
    }
}
