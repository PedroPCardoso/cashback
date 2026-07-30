<?php

declare(strict_types=1);

namespace App\Domain\Category\Repositories;

use App\Domain\Category\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(string $id): ?Category;

    /**
     * @return Category[]
     */
    public function all(): array;

    public function save(Category $category): void;

    public function delete(string $id): void;
}
