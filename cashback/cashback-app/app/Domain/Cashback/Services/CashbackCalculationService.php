<?php

declare(strict_types=1);

namespace App\Domain\Cashback\Services;

use App\Domain\Category\Entities\Category;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Transaction\ValueObjects\Money;

class CashbackCalculationService
{
    public function calculate(
        Category $category,
        Money $transactionValue,
        Money $currentMonthSpent
    ): CalculationResult {
        $limit = $category->monthlyLimit();
        $rate = $category->cashbackRate()->value();

        if ($limit->isUnlimited()) {
            return new CalculationResult(
                $this->calculateCashback($transactionValue, $rate),
                CategoryStatus::WITHIN_LIMIT
            );
        }

        $limitMoney = $limit->money();
        assert($limitMoney !== null);
        if ($currentMonthSpent->amountCents() >= $limitMoney->amountCents()) {
            return new CalculationResult(
                new Money(0, $transactionValue->currency()),
                CategoryStatus::EXCEEDED
            );
        }

        $remaining = $limitMoney->subtract($currentMonthSpent);

        $valueForCashback = $transactionValue;
        $status = CategoryStatus::WITHIN_LIMIT;

        if ($transactionValue->amountCents() > $remaining->amountCents()) {
            $valueForCashback = $remaining;
            $status = CategoryStatus::EXCEEDED;
        } elseif ($transactionValue->amountCents() === $remaining->amountCents()) {
            $status = CategoryStatus::EXCEEDED;
        }

        return new CalculationResult(
            $this->calculateCashback($valueForCashback, $rate),
            $status
        );
    }

    private function calculateCashback(Money $value, float $rate): Money
    {
        if ($rate <= 0) {
            return new Money(0, $value->currency());
        }

        // Calculation: (amount * rate) / 100
        // We handle cents, so (cents * rate) / 100.
        // e.g. 1000 cents (10.00) * 5.0 rate = 5000 / 100 = 50 cents (0.50)
        $cashbackCents = (int) round(($value->amountCents() * $rate) / 100);

        return new Money($cashbackCents, $value->currency());
    }
}
