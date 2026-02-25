<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Category\Services;

use App\Domain\Category\Entities\Category;
use App\Domain\Category\Services\CategoryCategorizationService;
use App\Domain\Category\ValueObjects\CategoryType;
use PHPUnit\Framework\TestCase;

class CategoryCategorizationServiceTest extends TestCase
{
    private CategoryCategorizationService $service;

    protected function setUp(): void
    {
        $this->service = new CategoryCategorizationService;
    }

    public function test_it_matches_single_keyword(): void
    {
        $cat1 = new Category('1', 'Alimentação', CategoryType::DEFAULT);
        $cat1->addKeyword('restaurante', 10);

        $cat2 = new Category('2', 'Transporte', CategoryType::DEFAULT);
        $cat2->addKeyword('uber', 10);

        $match = $this->service->categorize('Fui no restaurante hoje', [$cat1, $cat2]);

        $this->assertNotNull($match);
        $this->assertEquals('1', $match->id());
    }

    public function test_it_is_case_insensitive(): void
    {
        $cat1 = new Category('1', 'Alimentação', CategoryType::DEFAULT);
        $cat1->addKeyword('RESTAURANTE', 10);

        $match = $this->service->categorize('restaurante', [$cat1]);

        $this->assertNotNull($match);
        $this->assertEquals('1', $match->id());
    }

    public function test_it_picks_highest_priority_match(): void
    {
        $cat1 = new Category('1', 'Alimentação', CategoryType::DEFAULT);
        $cat1->addKeyword('almoço', 20); // Lower priority

        $cat2 = new Category('2', 'Lazer', CategoryType::CUSTOM);
        $cat2->addKeyword('almoço', 5); // Higher priority

        $match = $this->service->categorize('Despesa com almoço', [$cat1, $cat2]);

        $this->assertNotNull($match);
        $this->assertEquals('2', $match->id());
    }

    public function test_it_returns_null_if_no_match(): void
    {
        $cat1 = new Category('1', 'A', CategoryType::DEFAULT);
        $cat1->addKeyword('foo', 10);

        $match = $this->service->categorize('bar', [$cat1]);

        $this->assertNull($match);
    }
}
