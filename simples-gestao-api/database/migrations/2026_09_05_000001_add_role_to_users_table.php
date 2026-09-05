<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona o campo 'role' na tabela users.
 *
 * Por que uma migration separada e não modificar a migration original?
 * - A migration padrão do Laravel (users) é "sagrada" — alterá-la pode
 *   causar conflitos com pacotes que esperam a estrutura padrão.
 * - No Alembic (SQLAlchemy), seria equivalente a criar uma nova revision
 *   com op.add_column() ao invés de editar o modelo original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // enum() no Laravel cria um CHECK constraint no PostgreSQL
            // (diferente do MySQL que tem ENUM nativo).
            // Equivalente SQLAlchemy: Column(Enum('admin', 'operator'))
            $table->enum('role', ['admin', 'operator'])->default('operator')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
