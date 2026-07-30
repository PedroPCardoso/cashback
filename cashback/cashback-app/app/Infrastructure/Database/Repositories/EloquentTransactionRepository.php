<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Transaction\Entities\Transaction as DomainTransaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;
use App\Infrastructure\Database\Models\Transaction as EloquentTransaction;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findById(string $id): ?DomainTransaction
    {
        $eloquent = EloquentTransaction::find($id);

        if (! $eloquent) {
            return null;
        }

        return $this->toDomain($eloquent);
    }

    public function save(DomainTransaction $transaction): void
    {
        EloquentTransaction::updateOrCreate(
            ['id' => $transaction->id()],
            [
                'description' => $transaction->description(),
                'amount_cents' => $transaction->value()->amountCents(),
                'currency' => $transaction->value()->currency(),
                'date' => $transaction->date()->toDateTime()->format('Y-m-d'),
                'category_id' => $transaction->categoryId(),
            ]
        );
    }

    public function delete(string $id): void
    {
        EloquentTransaction::destroy($id);
    }

    public function findByMonth(int $year, int $month): array
    {
        return EloquentTransaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn (EloquentTransaction $e) => $this->toDomain($e))
            ->toArray();
    }

    public function findAll(): array
    {
        return EloquentTransaction::orderBy('date', 'desc')
            ->get()
            ->map(fn (EloquentTransaction $e) => $this->toDomain($e))
            ->toArray();
    }

    private function toDomain(EloquentTransaction $eloquent): DomainTransaction
    {
        return new DomainTransaction(
            id: $eloquent->id,
            description: $eloquent->description,
            value: new Money($eloquent->amount_cents, $eloquent->currency),
            date: new TransactionDate($eloquent->date->toDateTimeImmutable()),
            categoryId: $eloquent->category_id
        );
    }
}
