<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

use InvalidArgumentException;

readonly class Money
{
    public function __construct(
        private int $amountCents,
        private string $currency = 'BRL'
    ) {
        if ($amountCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public function amountCents(): int
    {
        return $this->amountCents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amountCents + $other->amountCents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        if ($this->amountCents < $other->amountCents) {
            throw new InvalidArgumentException('Subtraction result cannot be negative');
        }

        return new self($this->amountCents - $other->amountCents, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amountCents === $other->amountCents
            && $this->currency === $other->currency;
    }

    private function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}
