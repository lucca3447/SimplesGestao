<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'items_count' => $this->whenCounted('items'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
