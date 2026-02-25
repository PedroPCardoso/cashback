<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Api;

use App\Application\UseCases\Cashback\GetMonthlySummaryUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CashbackController extends Controller
{
    public function __construct(
        private GetMonthlySummaryUseCase $getMonthlySummaryUseCase
    ) {}

    public function summary(int $year, int $month): JsonResponse
    {
        $summary = $this->getMonthlySummaryUseCase->execute($year, $month);

        return response()->json($summary);
    }
}
