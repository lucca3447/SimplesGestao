<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela de pedidos/vendas.
 *
 * Duas FKs: customer (quem comprou) e user (quem registrou).
 *
 * customer_id é nullable porque nem toda venda tem cliente cadastrado
 * (venda de balcão). O ->nullOnDelete() garante que, se o cliente for
 * deletado, o pedido não é perdido — só fica sem vínculo.
 *
 * Equivalente SQLAlchemy para nullOnDelete:
 *   Column(BigInteger, ForeignKey('customers.id', ondelete='SET NULL'))
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Cliente (opcional — venda de balcão)
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // Usuário que registrou a venda
            $table->foreignId('user_id')->constrained()->onDelete('restrict');

            $table->string('order_number', 20)->unique();    // PED-20260905-001
            $table->enum('status', ['pending', 'confirmed', 'delivered', 'cancelled'])
                  ->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_method', [
                'cash', 'credit_card', 'debit_card', 'pix', 'bank_transfer'
            ])->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para consultas frequentes
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
