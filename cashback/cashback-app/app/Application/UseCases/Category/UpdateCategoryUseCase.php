<?php

declare(strict_types=1);

namespace App\Application\UseCases\Category;

use App\Application\DTOs\Category\UpdateCategoryRequest;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use Exception;

class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo
    ) {}

    public function execute(UpdateCategoryRequest $request): void
    {
        $category = $this->categoryRepo->findById($request->id);

        if (! $category) {
            throw new Exception('Category not found');
        }

        $name = $request->name ?? $category->name();
        $limit = $category->monthlyLimit();
        $rate = $category->cashbackRate();

        if ($request->monthlyLimitCents !== null) {
            $limit = MonthlyLimit::fixed(new Money($request->monthlyLimitCents));
        }

        if ($request->cashbackRate !== null) {
            $rate = new CashbackRate($request->cashbackRate);
        }

        $category->updateSettings($name, $limit, $rate);

        if ($request->keywords !== null) {
            $category->clearKeywords();
            foreach ($request->keywords as $index => $keyword) {
                $category->addKeyword($keyword, count($request->keywords) - $index);
            }
        }

        $this->categoryRepo->save($category);
    }
}
