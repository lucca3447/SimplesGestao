<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela de produtos com controle de estoque.
 *
 * foreignId('category_id') + constrained():
 *   - Cria a coluna bigint + a foreign key constraint automaticamente.
 *   - Equivalente SQLAlchemy:
 *       category_id = Column(BigInteger, ForeignKey('categories.id'))
 *
 * decimal(10, 2):
 *   - NUNCA use float para dinheiro! Erros de arredondamento são reais.
 *   - Equivalente SQLAlchemy: Column(Numeric(10, 2))
 *
 * ->default(0):
 *   - Define valor padrão no banco.
 *   - Equivalente SQLAlchemy: Column(Integer, default=0, server_default='0')
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // FK para categories — constrained() infere 'categories.id' pelo nome da coluna
            $table->foreignId('category_id')->constrained()->onDelete('restrict');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku', 50)->nullable()->unique();  // código interno
            $table->decimal('price', 10, 2);                   // preço de venda
            $table->decimal('cost_price', 10, 2)->nullable();  // preço de custo
            $table->integer('stock_quantity')->default(0);      // estoque atual
            $table->integer('min_stock')->default(0);           // estoque mínimo (alerta)
            $table->boolean('is_active')->default(true);        // soft-disable
            $table->timestamps();

            // Índice para filtrar por categoria (consultas frequentes)
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
