<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transaction;

use App\Application\DTOs\Transaction\RegisterTransactionRequest;
use App\Domain\Cashback\Entities\MonthlySummary;
use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Cashback\Services\CashbackCalculationService;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Category\Services\CategoryCategorizationService;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;
use Illuminate\Support\Str;

class RegisterTransactionUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private CategoryRepositoryInterface $categoryRepo,
        private MonthlySummaryRepositoryInterface $summaryRepo,
        private CategoryCategorizationService $categorizationService,
        private CashbackCalculationService $cashbackService
    ) {}

    public function execute(RegisterTransactionRequest $request): void
    {
        $date = TransactionDate::fromString($request->date);
        $money = new Money($request->amountCents);

        $categories = $this->categoryRepo->all();
        $matchedCategory = $this->categorizationService->categorize($request->description, $categories);

        $categoryId = $matchedCategory?->id();

        if ($matchedCategory) {
            $year = (int) $date->toDateTime()->format('Y');
            $month = (int) $date->toDateTime()->format('m');

            if ($categoryId === null) {
            return;
        }

            $summary = $this->summaryRepo->findForCategoryAndMonth($categoryId, $year, $month);

            if (! $summary) {
                $summary = new MonthlySummary(
                    $categoryId,
                    $year,
                    $month,
                    new Money(0),
                    new Money(0),
                    CategoryStatus::WITHIN_LIMIT
                );
            }

            $calcResult = $this->cashbackService->calculate(
                $matchedCategory,
                $money,
                $summary->totalSpent()
            );

            $summary->applyTransaction(
                $money,
                $calcResult->cashback(),
                $calcResult->newStatus()
            );

            $this->summaryRepo->save($summary);
        }

        $transaction = new Transaction(
            (string) Str::uuid(),
            $request->description,
            $money,
            $date,
            $categoryId
        );

        $this->transactionRepo->save($transaction);
    }
}
