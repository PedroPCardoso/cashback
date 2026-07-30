<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transaction;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class ListTransactionsUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo
    ) {}

    /**
     * @return \App\Domain\Transaction\Entities\Transaction[]
     */
    public function execute(): array
    {
        return $this->transactionRepo->findAll();
    }
}
