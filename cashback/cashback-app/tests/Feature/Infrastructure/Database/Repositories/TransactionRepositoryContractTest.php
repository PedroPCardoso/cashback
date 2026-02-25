<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Database\Repositories;

use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\Transaction\ValueObjects\TransactionDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRepositoryContractTest extends TestCase
{
    use RefreshDatabase;

    private TransactionRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(TransactionRepositoryInterface::class);
    }

    public function test_it_can_save_and_find_a_transaction(): void
    {
        $transaction = new Transaction(
            id: '550e8400-e29b-41d4-a716-446655440010',
            description: 'Lunch',
            value: new Money(1500),
            date: TransactionDate::fromString('2026-02-24'),
            categoryId: null
        );

        $this->repository->save($transaction);

        $found = $this->repository->findById('550e8400-e29b-41d4-a716-446655440010');
        assert($found !== null);
        $this->assertEquals('Lunch', $found->description());
        $this->assertEquals(1500, $found->value()->amountCents());
        $this->assertEquals('2026-02-24', $found->date()->toDateTime()->format('Y-m-d'));
    }

    public function test_it_can_find_transactions_by_month(): void
    {
        $t1 = new Transaction('550e8400-e29b-41d4-a716-446655440011', 'A', new Money(100), TransactionDate::fromString('2026-02-01'), null);
        $t2 = new Transaction('550e8400-e29b-41d4-a716-446655440012', 'B', new Money(100), TransactionDate::fromString('2026-02-28'), null);
        $t3 = new Transaction('550e8400-e29b-41d4-a716-446655440013', 'C', new Money(100), TransactionDate::fromString('2026-03-01'), null);

        $this->repository->save($t1);
        $this->repository->save($t2);
        $this->repository->save($t3);

        $results = $this->repository->findByMonth(2026, 2);
        $this->assertCount(2, $results);
        $this->assertNotNull($this->repository->findById('550e8400-e29b-41d4-a716-446655440011'));
    }
}
