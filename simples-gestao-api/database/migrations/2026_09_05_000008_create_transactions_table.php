<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transações financeiras — o coração do resumo de entradas × saídas.
 *
 * order_id é nullable: nem toda transação vem de uma venda.
 * Exemplos de transações sem pedido:
 *   - Aluguel (expense, sem order)
 *   - Salário funcionário (expense, sem order)
 *   - Aporte do dono (income, sem order)
 *
 * Quando uma venda é confirmada, o sistema gera automaticamente uma
 * transação de income vinculada ao pedido via order_id.
 *
 * amount é SEMPRE positivo — o type ('income'/'expense') determina
 * se entra ou sai. Isso simplifica queries de soma:
 *   SELECT SUM(amount) FROM transactions WHERE type = 'income'
 *   SELECT SUM(amount) FROM transactions WHERE type = 'expense'
 *   Lucro = income - expense
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_category_id')->constrained()->onDelete('restrict');

            // Vincula à venda quando a transação é gerada por um pedido
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 12, 2);           // sempre positivo
            $table->string('description');
            $table->date('transaction_date');            // data efetiva
            $table->timestamps();

            // Índices para relatórios por período e tipo
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
