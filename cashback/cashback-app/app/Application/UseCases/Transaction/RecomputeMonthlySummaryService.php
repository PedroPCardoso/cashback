<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transaction;

use App\Domain\Cashback\Entities\MonthlySummary;
use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Cashback\Services\CashbackCalculationService;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;

class RecomputeMonthlySummaryService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private MonthlySummaryRepositoryInterface $summaryRepo,
        private CategoryRepositoryInterface $categoryRepo,
        private CashbackCalculationService $cashbackCalcService
    ) {}

    public function execute(string $categoryId, int $year, int $month): void
    {
        $category = $this->categoryRepo->findById($categoryId);
        if (! $category) {
            return;
        }

        // Fetch all transactions for this month/year (could be optimized to filter by category in DB, but filtering here for now)
        $allTransactions = $this->transactionRepo->findByMonth($year, $month);
        $categoryTransactions = array_filter($allTransactions, fn ($t) => $t->categoryId() === $categoryId);

        // Sort by date ascending to simulate chronological order
        usort($categoryTransactions, fn ($a, $b) => $a->date()->toDateTime() <=> $b->date()->toDateTime());

        // Rebuild summary from scratch
        $summary = new MonthlySummary(
            $categoryId,
            $year,
            $month,
            new Money(0, 'BRL'),
            new Money(0, 'BRL'),
            \App\Domain\Category\ValueObjects\CategoryStatus::WITHIN_LIMIT
        );

        foreach ($categoryTransactions as $tx) {
            $result = $this->cashbackCalcService->calculate(
                $category,
                $tx->value(),
                $summary->totalSpent()
            );
            $summary->applyTransaction($tx->value(), $result->cashback(), $result->newStatus());
        }

        $this->summaryRepo->save($summary);
    }
}
