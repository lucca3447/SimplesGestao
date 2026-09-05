<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Transaction — Transação financeira (entrada ou saída).
 *
 * Pode estar vinculada a um pedido (venda gera income automaticamente)
 * ou ser avulsa (aluguel, salário, etc).
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_category_id',
        'order_id',
        'type',
        'amount',
        'description',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────

    public function financialCategory(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class);
    }

    /**
     * Pedido que gerou esta transação (nullable).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
