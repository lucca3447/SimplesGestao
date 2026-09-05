<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categorias financeiras — classifica transações como entrada ou saída.
 * Exemplos: "Vendas" (income), "Aluguel" (expense), "Salários" (expense).
 *
 * Separada de categories (produtos) porque são domínios diferentes:
 * - categories: classifica produtos (Bebidas, Eletrônicos)
 * - financial_categories: classifica fluxo de caixa (Vendas, Aluguel)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                               // "Vendas", "Aluguel"
            $table->enum('type', ['income', 'expense']);           // entrada ou saída
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};
