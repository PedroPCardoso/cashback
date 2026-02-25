<?php

declare(strict_types=1);

namespace App\Application\UseCases\Cashback;

use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;

class GetMonthlySummaryUseCase
{
    public function __construct(
        private MonthlySummaryRepositoryInterface $summaryRepo,
        private CategoryRepositoryInterface $categoryRepo
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $year, int $month): array
    {
        $summaries = $this->summaryRepo->findAllForMonth($year, $month);
        $categories = $this->categoryRepo->all();

        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[$cat->id()] = $cat;
        }

        $totalSpent = 0;
        $totalCashback = 0;
        $categoryDetails = [];

        foreach ($summaries as $summary) {
            $cat = $categoriesMap[$summary->categoryId()] ?? null;
            $catName = $cat ? $cat->name() : 'Unknown';

            $totalSpent += $summary->totalSpent()->amountCents();
            $totalCashback += $summary->cashbackEarned()->amountCents();

            $categoryDetails[] = [
                'category_id' => $summary->categoryId(),
                'category_name' => $catName,
                'total_spent' => $summary->totalSpent()->amountCents() / 100,
                'cashback_earned' => $summary->cashbackEarned()->amountCents() / 100,
                'status' => $summary->status()->value,
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'total_spent' => $totalSpent / 100,
            'total_cashback' => $totalCashback / 100,
            'categories' => $categoryDetails,
        ];
    }
}
