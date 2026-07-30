<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases\Transaction;

use App\Application\DTOs\Transaction\RegisterTransactionRequest;
use App\Application\UseCases\Transaction\RegisterTransactionUseCase;
use App\Domain\Cashback\Repositories\MonthlySummaryRepositoryInterface;
use App\Domain\Cashback\Services\CalculationResult;
use App\Domain\Cashback\Services\CashbackCalculationService;
use App\Domain\Category\Entities\Category;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Category\Services\CategoryCategorizationService;
use App\Domain\Category\ValueObjects\CategoryStatus;
use App\Domain\Category\ValueObjects\CategoryType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\Money;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RegisterTransactionUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var TransactionRepositoryInterface&Mockery\MockInterface */
    private $transactionRepo;

    /** @var CategoryRepositoryInterface&Mockery\MockInterface */
    private $categoryRepo;

    /** @var MonthlySummaryRepositoryInterface&Mockery\MockInterface */
    private $summaryRepo;

    /** @var CategoryCategorizationService&Mockery\MockInterface */
    private $categorizationService;

    /** @var CashbackCalculationService&Mockery\MockInterface */
    private $cashbackService;

    private RegisterTransactionUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->categoryRepo = Mockery::mock(CategoryRepositoryInterface::class);
        $this->summaryRepo = Mockery::mock(MonthlySummaryRepositoryInterface::class);
        $this->categorizationService = Mockery::mock(CategoryCategorizationService::class);
        $this->cashbackService = Mockery::mock(CashbackCalculationService::class);

        $this->useCase = new RegisterTransactionUseCase(
            $this->transactionRepo,
            $this->categoryRepo,
            $this->summaryRepo,
            $this->categorizationService,
            $this->cashbackService
        );
    }

    public function test_it_registers_a_transaction_without_category_match(): void
    {
        $request = new RegisterTransactionRequest('Unmatched Shop', 1000, '2026-02-24');

        $this->categoryRepo->shouldReceive('all')->andReturn([]);
        $this->categorizationService->shouldReceive('categorize')->andReturn(null);

        $this->transactionRepo->shouldReceive('save')->once()->with(Mockery::on(function ($transaction) {
            return $transaction->description() === 'Unmatched Shop' &&
                   $transaction->value()->amountCents() === 1000 &&
                   $transaction->categoryId() === null;
        }));

        $this->useCase->execute($request);
    }

    public function test_it_registers_a_transaction_with_category_match(): void
    {
        $request = new RegisterTransactionRequest('Uber Ride', 2000, '2026-02-24');

        $category = new Category('cat-123', 'Transporte', CategoryType::DEFAULT);

        $this->categoryRepo->shouldReceive('all')->andReturn([$category]);
        $this->categorizationService->shouldReceive('categorize')->andReturn($category);

        // Mocking MonthlySummary
        $this->summaryRepo->shouldReceive('findForCategoryAndMonth')
            ->with('cat-123', 2026, 2)
            ->andReturn(null); // No previous summary

        // Mocking Cashback Calculation
        $calcResult = new CalculationResult(new Money(100), CategoryStatus::WITHIN_LIMIT);
        $this->cashbackService->shouldReceive('calculate')
            ->andReturn($calcResult);

        $this->summaryRepo->shouldReceive('save')->once();

        $this->transactionRepo->shouldReceive('save')->once()->with(Mockery::on(function ($transaction) {
            return $transaction->categoryId() === 'cat-123';
        }));

        $this->useCase->execute($request);
    }
}
