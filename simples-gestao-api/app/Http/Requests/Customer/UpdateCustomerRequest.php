<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf_cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('customers', 'cpf_cnpj')->ignore($customerId),
            ],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
