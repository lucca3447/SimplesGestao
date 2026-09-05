<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model FinancialCategory — Categoria financeira.
 * Classifica transações como entrada (income) ou saída (expense).
 */
class FinancialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
