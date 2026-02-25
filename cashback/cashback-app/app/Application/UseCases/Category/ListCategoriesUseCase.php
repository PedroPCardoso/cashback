<?php

declare(strict_types=1);

namespace App\Application\UseCases\Category;

use App\Domain\Category\Repositories\CategoryRepositoryInterface;

class ListCategoriesUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo
    ) {}

    /**
     * @return \App\Domain\Category\Entities\Category[]
     */
    public function execute(): array
    {
        return $this->categoryRepo->all();
    }
}
