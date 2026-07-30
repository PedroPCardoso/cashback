<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Api;

use App\Application\DTOs\Category\UpdateCategoryRequest;
use App\Application\UseCases\Category\ListCategoriesUseCase;
use App\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private ListCategoriesUseCase $listCategoriesUseCase,
        private UpdateCategoryUseCase $updateCategoryUseCase
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->listCategoriesUseCase->execute();

        $data = array_map(function ($c) {
            $limitMoney = $c->monthlyLimit()->money();
            return [
                'id' => $c->id(),
                'name' => $c->name(),
                'type' => $c->type()->value,
                'cashback_rate' => $c->cashbackRate()->value(),
                'monthly_limit_cents' => $limitMoney ? $limitMoney->amountCents() : null,
                'keywords' => $c->keywords(),
            ];
        }, $categories);


        return response()->json($data);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->updateCategoryUseCase->execute(
            new UpdateCategoryRequest(
                $id,
                $request->string('name')->toString() ?: null,
                $request->has('cashback_rate') ? (float) $request->input('cashback_rate') : null,
                $request->has('monthly_limit') ? (int) ($request->input('monthly_limit') * 100) : null,
                $request->input('keywords')
            )
        );

        return response()->json(['message' => 'Category updated successfully']);
    }
}
