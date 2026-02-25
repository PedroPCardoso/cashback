<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Category\ValueObjects;

use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MonthlyLimitTest extends TestCase
{
    public function test_it_can_be_unlimited(): void
    {
        $limit = MonthlyLimit::unlimited();
        $this->assertTrue($limit->isUnlimited());
        $this->assertNull($limit->money());
    }

    public function test_it_can_have_a_money_value(): void
    {
        $money = new Money(50000);
        $limit = new MonthlyLimit($money);
        $this->assertFalse($limit->isUnlimited());
        $this->assertEquals($money, $limit->money());
    }
}
