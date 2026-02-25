<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Category\ValueObjects;

use App\Domain\Category\ValueObjects\CashbackRate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CashbackRateTest extends TestCase
{
    public function test_it_holds_a_percentage_value(): void
    {
        $rate = new CashbackRate(5.5);
        $this->assertEquals(5.5, $rate->value());
    }

    public function test_it_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CashbackRate(-0.1);
    }

    public function test_it_rejects_value_above_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CashbackRate(100.1);
    }

    public function test_it_accepts_0_and_100(): void
    {
        $this->assertEquals(0, (new CashbackRate(0))->value());
        $this->assertEquals(100, (new CashbackRate(100))->value());
    }
}
