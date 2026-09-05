<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Product — Produto com controle de estoque.
 *
 * Demonstra os dois lados de um relacionamento:
 * - belongsTo(Category) → "este produto PERTENCE A uma categoria"
 * - hasMany(OrderItem)  → "este produto TEM MUITOS itens de pedido"
 *
 * Equivalente SQLAlchemy:
 *   category_id = Column(ForeignKey('categories.id'))
 *   category = relationship("Category", back_populates="products")
 *   order_items = relationship("OrderItem", back_populates="product")
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'sku',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock',
        'is_active',
    ];

    /**
     * Casts convertem tipos automaticamente.
     * Sem cast, 'price' viria como string do banco (PostgreSQL retorna
     * DECIMAL como string pra não perder precisão). Com cast, vira float
     * no PHP pra facilitar operações.
     *
     * 'is_active' => 'boolean': converte 0/1 do banco pra true/false.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Um produto pertence a uma categoria.
     *
     * belongsTo = o INVERSO de hasMany.
     * Se Category hasMany Products, então Product belongsTo Category.
     *
     * O Laravel infere:
     *   - FK = 'category_id' (nome do método em snake_case + _id)
     *   - Tabela = 'categories' (plural do model referenciado)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Um produto aparece em muitos itens de pedido.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────

    /**
     * Verifica se o estoque está abaixo do mínimo.
     *
     * Esse tipo de método de negócio fica no Model — equivalente a
     * um @property no model SQLAlchemy ou um método no service layer.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }
}
