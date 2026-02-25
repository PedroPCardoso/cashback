<?php

declare(strict_types=1);

namespace App\Application\DTOs\Category;

readonly class UpdateCategoryRequest
{
    /**
     * @param  string[]|null  $keywords
     */
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?float $cashbackRate = null,
        public ?int $monthlyLimitCents = null,
        public ?array $keywords = null,
    ) {}
}
