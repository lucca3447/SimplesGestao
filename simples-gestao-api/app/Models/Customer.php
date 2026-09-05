<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Customer — Cliente do negócio.
 *
 * Todo Model no Laravel:
 * 1. Estende Illuminate\Database\Eloquent\Model
 * 2. O nome da tabela é inferido: Customer → 'customers' (plural snake_case)
 *    Equivalente SQLAlchemy: __tablename__ = 'customers'
 * 3. Assume que a PK é 'id' e que existem 'created_at' + 'updated_at'
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf_cnpj',
        'address',
        'notes',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Um cliente faz muitos pedidos.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
