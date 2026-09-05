<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Itens do pedido — tabela pivô entre orders e products.
 *
 * unit_price armazena o preço no MOMENTO da venda (snapshot).
 * Isso é crucial: se o produto mudar de preço amanhã, as vendas
 * antigas mantêm o preço correto. Padrão clássico de e-commerce/ERP.
 *
 * cascadeOnDelete(): quando o pedido é deletado, seus itens também são.
 * Equivalente SQLAlchemy: ForeignKey('orders.id', ondelete='CASCADE')
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Deleta itens quando o pedido é deletado
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Restringe deleção do produto se já foi vendido
            $table->foreignId('product_id')->constrained()->onDelete('restrict');

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);     // preço congelado no momento da venda
            $table->decimal('subtotal', 12, 2);        // quantity × unit_price
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
