<?php

declare(strict_types=1);

namespace App\Domain\Category\Entities;

use App\Domain\Category\ValueObjects\CashbackRate;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Category\ValueObjects\MonthlyLimit;

class Category
{
    /**
     * @param  array<int, array{keyword: string, priority: int}>  $keywords
     */
    public function __construct(
        private string $id,
        private string $name,
        private CategoryType $type,
        private MonthlyLimit $monthlyLimit = new MonthlyLimit,
        private CashbackRate $cashbackRate = new CashbackRate(0),
        private array $keywords = []
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): CategoryType
    {
        return $this->type;
    }

    public function monthlyLimit(): MonthlyLimit
    {
        return $this->monthlyLimit;
    }

    public function cashbackRate(): CashbackRate
    {
        return $this->cashbackRate;
    }

    /**
     * @return array<int, array{keyword: string, priority: int}>
     */
    public function keywords(): array
    {
        usort($this->keywords, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $this->keywords;
    }

    public function isDefault(): bool
    {
        return $this->type === CategoryType::DEFAULT;
    }

    public function canBeDeleted(): bool
    {
        return $this->type !== CategoryType::DEFAULT;
    }

    public function addKeyword(string $keyword, int $priority): void
    {
        $this->keywords[] = [
            'keyword' => $keyword,
            'priority' => $priority,
        ];
    }

    public function removeKeyword(string $keyword): void
    {
        $this->keywords = array_filter(
            $this->keywords,
            fn ($k) => $k['keyword'] !== $keyword
        );
    }

    public function clearKeywords(): void
    {
        $this->keywords = [];
    }

    public function updateSettings(
        string $name,
        MonthlyLimit $monthlyLimit,
        CashbackRate $cashbackRate
    ): void {
        $this->name = $name;
        $this->monthlyLimit = $monthlyLimit;
        $this->cashbackRate = $cashbackRate;
    }
}
