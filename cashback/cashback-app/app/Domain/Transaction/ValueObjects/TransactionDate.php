<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

readonly class TransactionDate
{
    public function __construct(
        private DateTimeImmutable $date
    ) {}

    public static function fromString(string $dateString): self
    {
        try {
            return new self(new DateTimeImmutable($dateString));
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid date string: '.$dateString);
        }
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->date;
    }

    public function yearMonth(): string
    {
        return $this->date->format('Y-m');
    }

    public function isSameMonth(self $other): bool
    {
        return $this->date->format('Y-m') === $other->date->format('Y-m');
    }
}
