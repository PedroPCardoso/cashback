<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Transaction;

use App\Infrastructure\Database\Models\Category;
use App\Infrastructure\Database\Models\MonthlySpendingSummary;
use App\Infrastructure\Database\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionManagementApiTest extends TestCase
{
    use RefreshDatabase;

    private string $categoryId;

    private string $transactionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryId = Str::uuid()->toString();
        Category::create([
            'id' => $this->categoryId,
            'name' => 'Test Category',
            'type' => 'default',
            'cashback_rate' => 5.0,
            'monthly_limit_cents' => 50000,
        ]);

        $this->transactionId = Str::uuid()->toString();
        Transaction::create([
            'id' => $this->transactionId,
            'description' => 'Test Transaction',
            'amount_cents' => 10000,
            'currency' => 'BRL',
            'date' => '2026-02-24',
            'category_id' => $this->categoryId,
        ]);

        MonthlySpendingSummary::create([
            'category_id' => $this->categoryId,
            'year' => 2026,
            'month' => 2,
            'total_spent_cents' => 10000,
            'cashback_earned_cents' => 500,
            'status' => 'within_limit',
        ]);
    }

    public function test_it_can_list_transactions(): void
    {
        $response = $this->getJson('/api/transactions');
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $this->transactionId)
            ->assertJsonPath('0.description', 'Test Transaction')
            ->assertJsonPath('0.amount_cents', 10000);
    }

    public function test_it_can_update_transaction(): void
    {
        $payload = [
            'description' => 'Updated Transaction',
            'amount_cents' => 20000,
        ];

        $response = $this->putJson("/api/transactions/{$this->transactionId}", $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $this->transactionId,
            'description' => 'Updated Transaction',
            'amount_cents' => 20000,
        ]);

        // Verify summary was re-calculated
        $this->assertDatabaseHas('monthly_spending_summaries', [
            'category_id' => $this->categoryId,
            'year' => 2026,
            'month' => 2,
            'total_spent_cents' => 20000,
            'cashback_earned_cents' => 1000,
        ]);
    }

    public function test_it_can_delete_transaction(): void
    {
        $response = $this->deleteJson("/api/transactions/{$this->transactionId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('transactions', [
            'id' => $this->transactionId,
        ]);

        // Summary should drop to 0 spent
        $this->assertDatabaseHas('monthly_spending_summaries', [
            'category_id' => $this->categoryId,
            'year' => 2026,
            'month' => 2,
            'total_spent_cents' => 0,
            'cashback_earned_cents' => 0,
        ]);
    }
}
