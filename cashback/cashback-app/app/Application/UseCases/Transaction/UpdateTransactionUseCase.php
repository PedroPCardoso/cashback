<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transaction;

use App\Application\DTOs\Transaction\UpdateTransactionRequest;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;
use DateTimeImmutable;
use Exception;

class UpdateTransactionUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private RecomputeMonthlySummaryService $recomputeService
    ) {}

    public function execute(UpdateTransactionRequest $request): void
    {
        $transaction = $this->transactionRepo->findById($request->id);

        if (! $transaction) {
            throw new Exception('Transaction not found');
        }

        // Store old tracking info to recompute its impact later
        $oldCategoryId = $transaction->categoryId();
        $oldDate = $transaction->date()->toDateTime();
        $oldYear = (int) $oldDate->format('Y');
        $oldMonth = (int) $oldDate->format('n');

        // New values
        $description = $request->description ?? $transaction->description();
        $value = $transaction->value();
        if ($request->amountCents !== null) {
            $currency = $request->currency ?? $value->currency();
            $value = new Money($request->amountCents, $currency);
        }
        $date = $transaction->date();
        if ($request->date !== null) {
            $date = new TransactionDate(new DateTimeImmutable($request->date));
        }
        $categoryId = $request->categoryId ?? $transaction->categoryId();

        // Update Entity (We create a new one to mimic mutation for now, or just save back if repository handles it)
        $updatedTransaction = new Transaction(
            $transaction->id(),
            $description,
            $value,
            $date,
            $categoryId
        );

        // Save
        $this->transactionRepo->save($updatedTransaction);

        // Extract new tracking info
        $newDate = $updatedTransaction->date()->toDateTime();
        $newYear = (int) $newDate->format('Y');
        $newMonth = (int) $newDate->format('n');

        // Rebuild old summary if it got affected
        // Rebuild old summary if it got affected
        if ($oldCategoryId !== null) {
            $this->recomputeService->execute($oldCategoryId, $oldYear, $oldMonth);
        }

        // Rebuild new summary if it got affected (and is different from old)
        if ($categoryId !== null && ($oldCategoryId !== $categoryId || $oldYear !== $newYear || $oldMonth !== $newMonth)) {
            $this->recomputeService->execute($categoryId, $newYear, $newMonth);
        }
    }
}
