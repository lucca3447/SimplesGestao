<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela de clientes.
 *
 * Comparação com SQLAlchemy:
 *   $table->id()              →  Column(BigInteger, primary_key=True, autoincrement=True)
 *   $table->string('name')    →  Column(String(255), nullable=False)
 *   $table->timestamps()      →  Column(DateTime, default=func.now()) × 2
 *   ->nullable()              →  nullable=True
 *   ->unique()                →  unique=True
 *
 * O método down() é o rollback — equivalente ao downgrade() do Alembic.
 * Ele desfaz exatamente o que up() fez, para poder reverter a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();                                    // bigint PK auto-increment
            $table->string('name');                          // varchar(255) NOT NULL
            $table->string('email')->nullable()->unique();   // varchar(255) UNIQUE, opcional
            $table->string('phone', 20)->nullable();         // varchar(20), opcional
            $table->string('cpf_cnpj', 18)->nullable()->unique(); // CPF/CNPJ formatado
            $table->text('address')->nullable();             // texto livre, opcional
            $table->text('notes')->nullable();               // observações
            $table->timestamps();                            // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
