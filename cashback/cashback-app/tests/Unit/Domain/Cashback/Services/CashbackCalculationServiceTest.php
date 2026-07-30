<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Cashback\Services;

use App\Domain\Cashback\Services\CashbackCalculationService;
use App\Domain\Category\Entities\Category;
use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class CashbackCalculationServiceTest extends TestCase
{
    private CashbackCalculationService $service;

    protected function setUp(): void
    {
        $this->service = new CashbackCalculationService;
    }

    public function test_it_calculates_full_cashback_when_well_under_limit(): void
    {
        $category = new Category('1', 'Test', CategoryType::CUSTOM, new MonthlyLimit(new Money(10000)), new CashbackRate(5.0));
        $transactionValue = new Money(1000); // 10.00
        $currentSpent = new Money(2000); // 20.00

        $result = $this->service->calculate($category, $transactionValue, $currentSpent);

        // 5% of 10.00 is 0.50 (50 cents)
        $this->assertEquals(50, $result->cashback()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $result->newStatus());
    }

    public function test_it_calculates_zero_cashback_when_already_exceeded(): void
    {
        $category = new Category('1', 'Test', CategoryType::CUSTOM, new MonthlyLimit(new Money(10000)), new CashbackRate(5.0));
        $transactionValue = new Money(1000);
        $currentSpent = new Money(10000); // Limit reached

        $result = $this->service->calculate($category, $transactionValue, $currentSpent);

        $this->assertEquals(0, $result->cashback()->amountCents());
        $this->assertEquals(CategoryStatus::EXCEEDED, $result->newStatus());
    }

    public function test_it_calculates_partial_cashback_when_crossing_limit(): void
    {
        $category = new Category('1', 'Test', CategoryType::CUSTOM, new MonthlyLimit(new Money(10000)), new CashbackRate(10.0));
        $transactionValue = new Money(5000); // 50.00
        $currentSpent = new Money(8000); // 80.00. Remaining: 2000 (20.00)

        $result = $this->service->calculate($category, $transactionValue, $currentSpent);

        // 10% of 2000 remaining is 200 cents
        $this->assertEquals(200, $result->cashback()->amountCents());
        $this->assertEquals(CategoryStatus::EXCEEDED, $result->newStatus());
    }

    public function test_it_calculates_full_cashback_when_unlimited(): void
    {
        $category = new Category('1', 'Test', CategoryType::CUSTOM, MonthlyLimit::unlimited(), new CashbackRate(10.0));
        $transactionValue = new Money(5000);
        $currentSpent = new Money(1000000);

        $result = $this->service->calculate($category, $transactionValue, $currentSpent);

        $this->assertEquals(500, $result->cashback()->amountCents());
        $this->assertEquals(CategoryStatus::WITHIN_LIMIT, $result->newStatus());
    }

    public function test_it_calculates_zero_cashback_when_rate_is_zero(): void
    {
        $category = new Category('1', 'Test', CategoryType::CUSTOM, MonthlyLimit::unlimited(), new CashbackRate(0.0));
        $transactionValue = new Money(5000);
        $currentSpent = new Money(0);

        $result = $this->service->calculate($category, $transactionValue, $currentSpent);

        $this->assertEquals(0, $result->cashback()->amountCents());
    }
}
