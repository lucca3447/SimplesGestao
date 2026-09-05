<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['sometimes', 'required', 'string', 'in:cash,credit_card,debit_card,pix,bank_transfer'],
            'notes' => ['nullable', 'string'],
            'discount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
