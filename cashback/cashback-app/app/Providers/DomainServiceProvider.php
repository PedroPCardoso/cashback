<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Domain\Category\Repositories\CategoryRepositoryInterface::class,
            \App\Infrastructure\Database\Repositories\EloquentCategoryRepository::class
        );

        $this->app->bind(
            \App\Domain\Transaction\Repositories\TransactionRepositoryInterface::class,
            \App\Infrastructure\Database\Repositories\EloquentTransactionRepository::class
        );

        $this->app->bind(
            \App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface::class,
            \App\Infrastructure\Database\Repositories\EloquentMonthlySummaryRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
