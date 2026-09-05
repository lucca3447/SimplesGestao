<?php

namespace App\Http\Requests\FinancialCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:income,expense'],
        ];
    }
}
