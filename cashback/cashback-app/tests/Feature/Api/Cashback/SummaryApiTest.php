<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Cashback;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_get_monthly_summary(): void
    {
        $categoryId = \Illuminate\Support\Str::uuid()->toString();
        \App\Infrastructure\Database\Models\Category::create([
            'id' => $categoryId,
            'name' => 'Alimentação',
            'type' => 'default',
            'cashback_rate' => 5.0,
        ]);

        \App\Infrastructure\Database\Models\MonthlySpendingSummary::create([
            'id' => 1,
            'category_id' => $categoryId,
            'year' => 2026,
            'month' => 2,
            'total_spent_cents' => 10000,
            'cashback_earned_cents' => 500,
            'status' => 'within_limit',
        ]);

        $response = $this->getJson('/api/summary/2026/2');

        $response->assertStatus(200)
            ->assertJson([
                'total_spent' => 100.00,
                'total_cashback' => 5.00,
                'categories' => [
                    [
                        'category_name' => 'Alimentação',
                        'total_spent' => 100.00,
                        'cashback_earned' => 5.00,
                        'status' => 'within_limit',
                    ],
                ],
            ]);
    }
}
