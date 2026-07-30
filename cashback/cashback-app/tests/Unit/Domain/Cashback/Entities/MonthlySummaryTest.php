<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Cashback\Entities;

use App\Domain\Cashback\Entities\MonthlySummary;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MonthlySummaryTest extends TestCase
{
    public function test_it_can_be_created_and_initialized(): void
    {
        $summary = new MonthlySummary(
            categoryId: 'cat-1',
            year: 2026,
            month: 2,
            totalSpent: new Money(0),
            cashbackEarned: new Money(0),
            status: CategoryStatus::WITHIN_LIMIT
        );

        $this->assertEquals('cat-1', $summary->categoryId());
        $this->assertEquals(2026, $summary->year());
        $this->assertEquals(2, $summary->month());
        $this->assertEquals(0, $summary->totalSpent()->amountCents());
        $this->assertEquals(0, $summary->cashbackEarned()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $summary->status());
    }

    public function test_it_can_apply_a_transaction(): void
    {
        $summary = new MonthlySummary('c1', 2026, 2, new Money(0), new Money(0), CategoryStatus::WITHIN_LIMIT);

        $summary->applyTransaction(new Money(1000), new Money(50), CategoryStatus::WITHIN_LIMIT);

        $this->assertEquals(1000, $summary->totalSpent()->amountCents());
        $this->assertEquals(50, $summary->cashbackEarned()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $summary->status());

        $summary->applyTransaction(new Money(500), new Money(0), CategoryStatus::EXCEEDED);

        $this->assertEquals(1500, $summary->totalSpent()->amountCents());
        $this->assertEquals(50, $summary->cashbackEarned()->amountCents());
        $this->assertEquals(CategoryStatus::EXCEEDED, $summary->status());
    }

    public function test_it_can_reverse_a_transaction(): void
    {
        $summary = new MonthlySummary('c1', 2026, 2, new Money(1500), new Money(50), CategoryStatus::EXCEEDED);

        $summary->reverseTransaction(new Money(500), new Money(0), CategoryStatus::WITHIN_LIMIT);

        $this->assertEquals(1000, $summary->totalSpent()->amountCents());
        $this->assertEquals(50, $summary->cashbackEarned()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $summary->status());
    }
}
