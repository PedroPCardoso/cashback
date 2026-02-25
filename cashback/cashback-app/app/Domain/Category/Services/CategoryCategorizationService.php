<?php

declare(strict_types=1);

namespace App\Domain\Category\Services;

use App\Domain\Category\Entities\Category;

class CategoryCategorizationService
{
    /**
     * @param  Category[]  $categories
     */
    public function categorize(string $description, array $categories): ?Category
    {
        $matches = [];
        $description = mb_strtolower($description);

        foreach ($categories as $category) {
            foreach ($category->keywords() as $keywordData) {
                $keyword = mb_strtolower($keywordData['keyword']);
                if (str_contains($description, $keyword)) {
                    $matches[] = [
                        'category' => $category,
                        'priority' => $keywordData['priority'],
                    ];
                }
            }
        }

        if (empty($matches)) {
            return null;
        }

        // Sort by priority (lowest int = highest priority)
        usort($matches, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $matches[0]['category'];
    }
}
