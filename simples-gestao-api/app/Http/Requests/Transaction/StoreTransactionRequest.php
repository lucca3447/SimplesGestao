<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:income,expense'],
            'financial_category_id' => [
                'required',
                'integer',
                Rule::exists('financial_categories', 'id')->where(function ($query) {
                    if ($this->filled('type')) {
                        $query->where('type', $this->input('type'));
                    }
                }),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
        ];
    }
}
