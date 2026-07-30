<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Database\Repositories;

use App\Domain\Category\Entities\Category;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryContractTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(CategoryRepositoryInterface::class);
    }

    public function test_it_can_save_and_find_a_category(): void
    {
        $category = new Category(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Alimentação',
            type: CategoryType::DEFAULT,
            monthlyLimit: new MonthlyLimit(new Money(100000)),
            cashbackRate: new CashbackRate(5.0)
        );
        $category->addKeyword('restaurante', 10);

        $this->repository->save($category);

        $found = $this->repository->findById('550e8400-e29b-41d4-a716-446655440000');
        assert($found !== null);

        $this->assertEquals('Alimentação', $found->name());
        $this->assertEquals(CategoryType::DEFAULT, $found->type());
        $limitMoney = $found->monthlyLimit()->money();
        $this->assertEquals(100000, $limitMoney ? $limitMoney->amountCents() : null);

        // Update test
        $this->assertEquals(5.0, $found->cashbackRate()->value());
        $this->assertCount(1, $found->keywords());
        $id = '550e8400-e29b-41d4-a716-446655440001';
        $category = new Category($id, 'Old', CategoryType::CUSTOM);
        $this->repository->save($category);

        $category->updateSettings('New', new MonthlyLimit(new Money(50)), new CashbackRate(10));
        $this->repository->save($category);

        $found = $this->repository->findById($id);
        assert($found !== null);
        $this->assertEquals('New', $found->name());
        $this->assertEquals(10.0, $found->cashbackRate()->value());
    }

    public function test_it_can_delete_a_category(): void
    {
        $id = '550e8400-e29b-41d4-a716-446655440002';
        $category = new Category($id, 'A', CategoryType::CUSTOM);
        $this->repository->save($category);
        $this->assertNotNull($this->repository->findById($id));

        $this->repository->delete($id);
        $this->assertNull($this->repository->findById($id));
    }
}
