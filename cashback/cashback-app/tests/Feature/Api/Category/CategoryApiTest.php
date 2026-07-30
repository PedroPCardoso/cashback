<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Category;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_categories(): void
    {
        $categoryId = \Illuminate\Support\Str::uuid()->toString();
        \App\Infrastructure\Database\Models\Category::create([
            'id' => $categoryId,
            'name' => 'Alimentação',
            'type' => 'default',
            'cashback_rate' => 5.0,
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Alimentação',
                'cashback_rate' => 5.0,
            ]);
    }

    public function test_it_can_update_category_settings(): void
    {
        $categoryId = \Illuminate\Support\Str::uuid()->toString();
        \App\Infrastructure\Database\Models\Category::create([
            'id' => $categoryId,
            'name' => 'Old Name',
            'type' => 'default',
            'cashback_rate' => 1.0,
        ]);

        $response = $this->putJson("/api/categories/$categoryId", [
            'name' => 'New Name',
            'cashback_rate' => 10.0,
            'monthly_limit' => 200.00,
            'keywords' => ['tag1', 'tag2'],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'name' => 'New Name',
            'cashback_rate' => 10.0,
            'monthly_limit_cents' => 20000,
        ]);

        $this->assertDatabaseHas('category_keyword_rules', [
            'category_id' => $categoryId,
            'keyword' => 'tag1',
        ]);
    }
}
