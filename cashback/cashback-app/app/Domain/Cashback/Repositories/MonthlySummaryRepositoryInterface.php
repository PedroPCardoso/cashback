<?php

declare(strict_types=1);

namespace App\Domain\Cashback\Repositories;

use App\Domain\Cashback\Entities\MonthlySummary;

interface MonthlySummaryRepositoryInterface
{
    public function findForCategoryAndMonth(string $categoryId, int $year, int $month): ?MonthlySummary;

    /**
     * @return MonthlySummary[]
     */
    public function findAllForMonth(int $year, int $month): array;

    public function save(MonthlySummary $summary): void;
}
