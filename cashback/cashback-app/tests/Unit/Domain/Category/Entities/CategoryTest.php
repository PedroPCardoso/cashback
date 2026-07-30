<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Category\Entities;

use App\Domain\Category\Entities\Category;
use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function test_it_can_be_created_with_id_name_and_type(): void
    {
        $category = new Category(
            id: 'uuid-1',
            name: 'Alimentação',
            type: CategoryType::DEFAULT
        );

        $this->assertEquals('uuid-1', $category->id());
        $this->assertEquals('Alimentação', $category->name());
        $this->assertEquals(CategoryType::DEFAULT, $category->type());
        $this->assertTrue($category->isDefault());
        $this->assertFalse($category->canBeDeleted());
    }

    public function test_custom_category_can_be_deleted(): void
    {
        $category = new Category(
            id: 'uuid-2',
            name: 'Custom',
            type: CategoryType::CUSTOM
        );

        $this->assertFalse($category->isDefault());
        $this->assertTrue($category->canBeDeleted());
    }

    public function test_it_retains_limit_and_cashback_rate(): void
    {
        $limit = new MonthlyLimit(new Money(50000));
        $rate = new CashbackRate(5.0);

        $category = new Category(
            id: 'uuid-1',
            name: 'Alimentação',
            type: CategoryType::DEFAULT,
            monthlyLimit: $limit,
            cashbackRate: $rate
        );

        $this->assertEquals($limit, $category->monthlyLimit());
        $this->assertEquals($rate, $category->cashbackRate());
    }

    public function test_it_manages_keywords(): void
    {
        $category = new Category(id: '1', name: 'A', type: CategoryType::CUSTOM);

        $category->addKeyword('restaurante', 10);
        $category->addKeyword('almoço', 5);

        $keywords = $category->keywords();
        $this->assertCount(2, $keywords);
        // Ordered by priority (lowest int first)
        $this->assertEquals('almoço', $keywords[0]['keyword']);
        $this->assertEquals('restaurante', $keywords[1]['keyword']);

        $category->removeKeyword('almoço');
        $this->assertCount(1, $category->keywords());
    }

    public function test_it_can_update_settings(): void
    {
        $category = new Category(id: '1', name: 'A', type: CategoryType::CUSTOM);
        $newLimit = new MonthlyLimit(new Money(100));
        $newRate = new CashbackRate(10);

        $category->updateSettings(
            name: 'New Name',
            monthlyLimit: $newLimit,
            cashbackRate: $newRate
        );

        $this->assertEquals('New Name', $category->name());
        $this->assertEquals($newLimit, $category->monthlyLimit());
        $this->assertEquals($newRate, $category->cashbackRate());
    }
}
