<?php

declare(strict_types=1);

namespace App\Application\DTOs\Transaction;

readonly class UpdateTransactionRequest
{
    public function __construct(
        public string $id,
        public ?string $description,
        public ?int $amountCents,
        public ?string $currency,
        public ?string $date,
        public ?string $categoryId
    ) {}
}
