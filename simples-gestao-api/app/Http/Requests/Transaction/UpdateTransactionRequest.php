<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'string', 'in:income,expense'],
            'financial_category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('financial_categories', 'id')->where(function ($query) {
                    $type = $this->input('type') ?? $this->route('transaction')?->type;
                    if ($type) {
                        $query->where('type', $type);
                    }
                }),
            ],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'transaction_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
