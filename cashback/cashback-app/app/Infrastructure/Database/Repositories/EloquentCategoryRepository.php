<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Category\Entities\Category as DomainCategory;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Category\ValueObjects\MonthlyLimit;
use App\Domain\Transaction\ValueObjects\Money;
use App\Infrastructure\Database\Models\Category as EloquentCategory;
use App\Infrastructure\Database\Models\CategoryKeywordRule;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(string $id): ?DomainCategory
    {
        $eloquent = EloquentCategory::with('keywordRules')->find($id);

        if (! $eloquent) {
            return null;
        }

        return $this->toDomain($eloquent);
    }

    public function all(): array
    {
        return EloquentCategory::with('keywordRules')
            ->get()
            ->map(fn (EloquentCategory $e) => $this->toDomain($e))
            ->toArray();
    }

    public function save(DomainCategory $category): void
    {
        $eloquent = EloquentCategory::updateOrCreate(
            ['id' => $category->id()],
            [
                'name' => $category->name(),
                'type' => $category->type()->value,
                'monthly_limit_cents' => $category->monthlyLimit()->money()?->amountCents(),
                'cashback_rate' => $category->cashbackRate()->value(),
            ]
        );

        $eloquent->keywordRules()->delete();

        /** @var array{keyword: string, priority: int} $keyword */
        foreach ($category->keywords() as $keyword) {
            $eloquent->keywordRules()->create([
                'keyword' => $keyword['keyword'],
                'priority' => $keyword['priority'],
            ]);
        }
    }

    public function delete(string $id): void
    {
        EloquentCategory::destroy($id);
    }

    private function toDomain(EloquentCategory $eloquent): DomainCategory
    {
        $limit = $eloquent->monthly_limit_cents !== null
            ? new MonthlyLimit(new Money($eloquent->monthly_limit_cents))
            : MonthlyLimit::unlimited();

        $category = new DomainCategory(
            id: $eloquent->id,
            name: $eloquent->name,
            type: CategoryType::from($eloquent->type),
            monthlyLimit: $limit,
            cashbackRate: new CashbackRate((float) $eloquent->cashback_rate)
        );

        /** @var CategoryKeywordRule $rule */
        foreach ($eloquent->keywordRules as $rule) {
            $category->addKeyword($rule->keyword, $rule->priority);
        }

        return $category;
    }
}
