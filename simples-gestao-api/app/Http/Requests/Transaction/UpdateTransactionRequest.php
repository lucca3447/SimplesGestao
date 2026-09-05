<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_category_id' => ['sometimes', 'required', 'integer', 'exists:financial_categories,id'],
            'type' => ['sometimes', 'required', 'string', 'in:income,expense'],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'transaction_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
