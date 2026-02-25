<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function findById(string $id): ?Transaction;

    public function save(Transaction $transaction): void;

    public function delete(string $id): void;

    /**
     * @return Transaction[]
     */
    public function findByMonth(int $year, int $month): array;

    /**
     * @return Transaction[]
     */
    public function findAll(): array;
}
