<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transaction;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use Exception;

class DeleteTransactionUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private RecomputeMonthlySummaryService $recomputeService
    ) {}

    public function execute(string $transactionId): void
    {
        $transaction = $this->transactionRepo->findById($transactionId);

        if (! $transaction) {
            throw new Exception('Transaction not found');
        }

        $categoryId = $transaction->categoryId();
        $date = $transaction->date()->toDateTime();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        $this->transactionRepo->delete($transactionId);

        $this->transactionRepo->delete($transactionId);

        if ($categoryId !== null) {
            $this->recomputeService->execute($categoryId, $year, $month);
        }
    }
}
