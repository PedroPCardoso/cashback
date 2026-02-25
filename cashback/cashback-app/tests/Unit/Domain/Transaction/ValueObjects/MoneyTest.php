<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transaction\ValueObjects;

use App\Domain\Transaction\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_can_be_created_with_amount_and_currency(): void
    {
        $money = new Money(1000, 'BRL');
        $this->assertEquals(1000, $money->amountCents());
        $this->assertEquals('BRL', $money->currency());
    }

    public function test_it_defaults_to_brl(): void
    {
        $money = new Money(1000);
        $this->assertEquals('BRL', $money->currency());
    }

    public function test_it_rejects_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money(-1);
    }

    public function test_it_can_be_added(): void
    {
        $m1 = new Money(1000);
        $m2 = new Money(500);
        $result = $m1->add($m2);

        $this->assertEquals(1500, $result->amountCents());
        $this->assertNotSame($m1, $result);
    }

    public function test_it_can_be_subtracted(): void
    {
        $m1 = new Money(1000);
        $m2 = new Money(300);
        $result = $m1->subtract($m2);

        $this->assertEquals(700, $result->amountCents());
    }

    public function test_it_rejects_subtraction_resulting_in_negative(): void
    {
        $m1 = new Money(100);
        $m2 = new Money(200);

        $this->expectException(InvalidArgumentException::class);
        $m1->subtract($m2);
    }

    public function test_it_rejects_operations_with_different_currencies(): void
    {
        $m1 = new Money(1000, 'BRL');
        $m2 = new Money(500, 'USD');

        $this->expectException(InvalidArgumentException::class);
        $m1->add($m2);
    }

    public function test_it_can_compare_equality(): void
    {
        $m1 = new Money(1000, 'BRL');
        $m2 = new Money(1000, 'BRL');
        $m3 = new Money(500, 'BRL');
        $m4 = new Money(1000, 'USD');

        $this->assertTrue($m1->equals($m2));
        $this->assertFalse($m1->equals($m3));
        $this->assertFalse($m1->equals($m4));
    }
}
