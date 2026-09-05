<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Order — Pedido/Venda.
 *
 * O model mais complexo do sistema: tem múltiplos relacionamentos
 * e lógica de negócio.
 *
 * Relacionamentos:
 *   - belongsTo Customer (quem comprou — opcional)
 *   - belongsTo User (quem registrou)
 *   - hasMany OrderItem (itens do pedido)
 *   - hasMany Transaction (transações financeiras geradas)
 */
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

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * O cliente que fez o pedido (pode ser null — venda de balcão).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * O usuário que registrou a venda.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Itens do pedido.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Transações financeiras vinculadas a este pedido.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────

    /**
     * Recalcula subtotal e total com base nos itens.
     * Chamado depois de adicionar/remover itens.
     */
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total = $this->subtotal - $this->discount;
        $this->save();
    }

    /**
     * Gera um número de pedido único.
     * Formato: PED-YYYYMMDD-NNN (ex: PED-20260905-001)
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = static::where('order_number', 'like', "PED-{$date}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "PED-{$date}-{$nextNumber}";
    }
}
