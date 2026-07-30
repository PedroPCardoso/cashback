<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Cashback\Entities\MonthlySummary as DomainSummary;
use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;
use App\Infrastructure\Database\Models\MonthlySpendingSummary as EloquentSummary;

class EloquentMonthlySummaryRepository implements MonthlySummaryRepositoryInterface
{
    public function findForCategoryAndMonth(string $categoryId, int $year, int $month): ?DomainSummary
    {
        $eloquent = EloquentSummary::where('category_id', $categoryId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (! $eloquent) {
            return null;
        }

        return $this->toDomain($eloquent);
    }

    public function save(DomainSummary $summary): void
    {
        EloquentSummary::updateOrCreate(
            [
                'category_id' => $summary->categoryId(),
                'year' => $summary->year(),
                'month' => $summary->month(),
            ],
            [
                'total_spent_cents' => $summary->totalSpent()->amountCents(),
                'cashback_earned_cents' => $summary->cashbackEarned()->amountCents(),
                'status' => $summary->status()->value,
            ]
        );
    }

    public function findAllForMonth(int $year, int $month): array
    {
        return EloquentSummary::where('year', $year)
            ->where('month', $month)
            ->get()
            ->map(fn (EloquentSummary $e) => $this->toDomain($e))
            ->toArray();
    }

    private function toDomain(EloquentSummary $eloquent): DomainSummary
    {
        return new DomainSummary(
            categoryId: $eloquent->category_id,
            year: (int) $eloquent->year,
            month: (int) $eloquent->month,
            totalSpent: new Money($eloquent->total_spent_cents),
            cashbackEarned: new Money($eloquent->cashback_earned_cents),
            status: CategoryStatus::from($eloquent->status)
        );
    }
}
