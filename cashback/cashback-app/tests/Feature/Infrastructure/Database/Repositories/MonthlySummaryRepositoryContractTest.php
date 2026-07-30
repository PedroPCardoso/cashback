<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Database\Repositories;

use App\Domain\Cashback\Entities\MonthlySummary;
use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlySummaryRepositoryContractTest extends TestCase
{
    use RefreshDatabase;

    private MonthlySummaryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(MonthlySummaryRepositoryInterface::class);
    }

    public function test_it_can_save_and_find_summary(): void
    {
        $catId = '550e8400-e29b-41d4-a716-446655440020';
        // Create category first
        $categoryRepo = $this->app->make(\App\Domain\Category\Repositories\CategoryRepositoryInterface::class);
        $categoryRepo->save(new \App\Domain\Category\Entities\Category($catId, 'A', \App\Domain\Category\ValueObjects\CategoryType::CUSTOM));

        $summary = new MonthlySummary(
            categoryId: $catId,
            year: 2026,
            month: 2,
            totalSpent: new Money(1000),
            cashbackEarned: new Money(50),
            status: CategoryStatus::WITHIN_LIMIT
        );

        $this->repository->save($summary);

        $found = $this->repository->findForCategoryAndMonth($catId, 2026, 2);

        $this->assertNotNull($found);
        $this->assertEquals(1000, $found->totalSpent()->amountCents());
        $this->assertEquals(50, $found->cashbackEarned()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $found->status());
    }
}
