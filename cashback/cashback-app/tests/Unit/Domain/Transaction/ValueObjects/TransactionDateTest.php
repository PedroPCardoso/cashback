<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transaction\ValueObjects;

use App\Domain\Transaction\ValueObjects\TransactionDate;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TransactionDateTest extends TestCase
{
    public function test_it_wraps_datetime_immutable(): void
    {
        $now = new DateTimeImmutable;
        $date = new TransactionDate($now);
        $this->assertEquals($now, $date->toDateTime());
    }

    public function test_it_returns_year_month_string(): void
    {
        $date = new TransactionDate(new DateTimeImmutable('2026-02-24'));
        $this->assertEquals('2026-02', $date->yearMonth());
    }

    public function test_it_can_compare_months(): void
    {
        $d1 = new TransactionDate(new DateTimeImmutable('2026-02-10'));
        $d2 = new TransactionDate(new DateTimeImmutable('2026-02-28'));
        $d3 = new TransactionDate(new DateTimeImmutable('2026-03-01'));

        $this->assertTrue($d1->isSameMonth($d2));
        $this->assertFalse($d1->isSameMonth($d3));
    }

    public function test_it_can_be_created_from_string(): void
    {
        $date = TransactionDate::fromString('2026-02-24');
        $this->assertEquals('2026-02-24', $date->toDateTime()->format('Y-m-d'));
    }
}
