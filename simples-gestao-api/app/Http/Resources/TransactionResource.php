<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'financial_category_id' => $this->financial_category_id,
            'financial_category' => new FinancialCategoryResource($this->whenLoaded('financialCategory')),
            'order_id' => $this->order_id,
            'order' => new OrderResource($this->whenLoaded('order')),
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
