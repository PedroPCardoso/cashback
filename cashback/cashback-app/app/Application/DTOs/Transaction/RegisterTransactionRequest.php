<?php

declare(strict_types=1);

namespace App\Application\DTOs\Transaction;

readonly class RegisterTransactionRequest
{
    public function __construct(
        public string $description,
        public int $amountCents,
        public string $date, // Y-m-d
    ) {}
}
