<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTransactionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function amountCents(): int
    {
        return (int) round($this->float('amount') * 100);
    }
}
