<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Transaction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_register_a_transaction_via_api(): void
    {
        $response = $this->postJson('/api/transactions', [
            'description' => 'Mercado Central',
            'amount' => 50.75,
            'date' => '2026-02-24',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Transaction registered successfully',
            ]);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Mercado Central',
            'amount_cents' => 5075,
            'date' => '2026-02-24',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/transactions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description', 'amount', 'date']);
    }

    public function test_it_automagically_categorizes_transaction(): void
    {
        // 1. Create a category with keywords
        $categoryId = \Illuminate\Support\Str::uuid()->toString();
        \App\Infrastructure\Database\Models\Category::create([
            'id' => $categoryId,
            'name' => 'Alimentação',
            'type' => 'default',
            'cashback_rate' => 5.0,
        ]);

        \App\Infrastructure\Database\Models\CategoryKeywordRule::create([
            'category_id' => $categoryId,
            'keyword' => 'mercado',
            'priority' => 1,
        ]);

        // 2. Register transaction
        $response = $this->postJson('/api/transactions', [
            'description' => 'Compra no Mercado',
            'amount' => 100.00,
            'date' => '2026-02-24',
        ]);

        $response->assertStatus(201);

        // 3. Assert category was assigned
        $this->assertDatabaseHas('transactions', [
            'description' => 'Compra no Mercado',
            'category_id' => $categoryId,
        ]);

        // 4. Assert summary was created/updated
        $this->assertDatabaseHas('monthly_spending_summaries', [
            'category_id' => $categoryId,
            'year' => 2026,
            'month' => 2,
            'total_spent_cents' => 10000,
            'cashback_earned_cents' => 500, // 5% of 100.00
        ]);
    }
}
