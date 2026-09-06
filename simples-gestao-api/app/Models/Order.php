<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Recalcula subtotal e total com base nos itens.
     */
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total = max(0, (float) $this->subtotal - (float) ($this->discount ?? 0));
        $this->save();
    }

    /**
     * Gera número de pedido sequencial: PED-YYYYMMDD-NNN
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PED-{$date}-";

        $lastOrder = static::where('order_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(order_number) DESC, order_number DESC')
            ->first();

        if ($lastOrder) {
            $parts = explode('-', $lastOrder->order_number);
            $lastNumber = (int) end($parts);
            $nextNumber = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
