<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Database\Models\Category;
use App\Infrastructure\Database\Models\CategoryKeywordRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Alimentação',
                'type' => 'default',
                'cashback_rate' => 5.0,
                'limit' => 50000, // 500.00
                'keywords' => ['mercado', 'restaurante', 'food', 'ifood', 'padaria'],
            ],
            [
                'name' => 'Transporte',
                'type' => 'default',
                'cashback_rate' => 3.0,
                'limit' => 20000, // 200.00
                'keywords' => ['uber', '99app', 'posto', 'gasolina', 'combustivel'],
            ],
            [
                'name' => 'Assinaturas',
                'type' => 'default',
                'cashback_rate' => 10.0,
                'limit' => 5000, // 50.00
                'keywords' => ['netflix', 'spotify', 'disney', 'prime', 'youtube'],
            ],
        ];

        foreach ($categories as $catData) {
            $cat = Category::create([
                'id' => Str::uuid()->toString(),
                'name' => $catData['name'],
                'type' => $catData['type'],
                'cashback_rate' => $catData['cashback_rate'],
                'monthly_limit_cents' => $catData['limit'] ?? null,
            ]);

            foreach ($catData['keywords'] as $index => $keyword) {
                CategoryKeywordRule::create([
                    'category_id' => $cat->id,
                    'keyword' => $keyword,
                    'priority' => count($catData['keywords']) - $index,
                ]);
            }
        }
    }
}
