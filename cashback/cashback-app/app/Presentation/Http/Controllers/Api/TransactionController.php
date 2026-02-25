<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Api;

use App\Application\DTOs\Transaction\RegisterTransactionRequest as UseCaseRequest;
use App\Application\UseCases\Transaction\RegisterTransactionUseCase;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Transaction\RegisterTransactionRequest;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private RegisterTransactionUseCase $registerTransactionUseCase,
        private \App\Application\UseCases\Transaction\ListTransactionsUseCase $listTransactionsUseCase,
        private \App\Application\UseCases\Transaction\UpdateTransactionUseCase $updateTransactionUseCase,
        private \App\Application\UseCases\Transaction\DeleteTransactionUseCase $deleteTransactionUseCase
    ) {}

    public function index(): JsonResponse
    {
        $transactions = $this->listTransactionsUseCase->execute();

        $data = array_map(function (\App\Domain\Transaction\Entities\Transaction $t) {
            return [
                'id' => $t->id(),
                'description' => $t->description(),
                'amount_cents' => $t->value()->amountCents(),
                'currency' => $t->value()->currency(),
                'date' => $t->date()->toDateTime()->format('Y-m-d'),
                'category_id' => $t->categoryId(),
            ];
        }, $transactions);

        return response()->json($data);
    }

    public function store(RegisterTransactionRequest $request): JsonResponse
    {
        $this->registerTransactionUseCase->execute(
            new UseCaseRequest(
                $request->string('description')->toString(),
                $request->amountCents(),
                $request->string('date')->toString()
            )
        );

        return response()->json([
            'message' => 'Transaction registered successfully',
        ], 201);
    }

    public function update(\Illuminate\Http\Request $request, string $id): JsonResponse
    {
        $this->updateTransactionUseCase->execute(
            new \App\Application\DTOs\Transaction\UpdateTransactionRequest(
                $id,
                $request->has('description') ? $request->string('description')->toString() : null,
                $request->has('amount_cents') ? (int) $request->input('amount_cents') : null,
                $request->has('currency') ? $request->string('currency')->toString() : null,
                $request->has('date') ? $request->string('date')->toString() : null,
                $request->has('category_id') ? $request->string('category_id')->toString() : null
            )
        );

        return response()->json(['message' => 'Transaction updated successfully']);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteTransactionUseCase->execute($id);

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
