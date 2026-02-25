<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transaction\Entities;

use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test_it_can_be_created_with_id_description_value_date_and_category(): void
    {
        $value = new Money(1000);
        $date = TransactionDate::fromString('2026-02-24');

        $transaction = new Transaction(
            id: 'unid-1',
            description: 'Lunch',
            value: $value,
            date: $date,
            categoryId: 'cat-1'
        );

        $this->assertEquals('unid-1', $transaction->id());
        $this->assertEquals('Lunch', $transaction->description());
        $this->assertEquals($value, $transaction->value());
        $this->assertEquals($date, $transaction->date());
        $this->assertEquals('cat-1', $transaction->categoryId());
    }

    public function test_it_can_change_category_value_and_date(): void
    {
        $transaction = new Transaction(
            id: '1',
            description: 'A',
            value: new Money(10),
            date: TransactionDate::fromString('2026-01-01'),
            categoryId: 'c1'
        );

        $newVal = new Money(20);
        $newDate = TransactionDate::fromString('2026-02-01');

        $transaction->changeCategory('c2');
        $transaction->changeValue($newVal);
        $transaction->changeDate($newDate);

        $this->assertEquals('c2', $transaction->categoryId());
        $this->assertEquals($newVal, $transaction->value());
        $this->assertEquals($newDate, $transaction->date());
    }
}
