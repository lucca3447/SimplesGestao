<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User — Usuário do sistema.
 *
 * Diferente dos outros Models, User estende Authenticatable (não Model).
 * Isso adiciona métodos de autenticação que o Sanctum vai usar depois.
 *
 * Equivalente Python:
 *   class User(Base):
 *       __tablename__ = 'users'
 *       id = Column(BigInteger, primary_key=True)
 *       name = Column(String, nullable=False)
 *       ...
 *       orders = relationship("Order", back_populates="user")
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Campos que podem ser preenchidos via mass assignment.
     *
     * Mass assignment = passar um array de dados de uma vez:
     *   User::create(['name' => 'João', 'email' => '...'])
     *
     * Equivalente SQLAlchemy: não tem — no Python você define campo a campo.
     * No Laravel, $fillable é uma proteção contra injection de campos
     * indesejados (ex: alguém tentando passar 'role' => 'admin' no request).
     *
     * Regra: só coloque aqui campos que o USUÁRIO pode enviar via formulário.
     * Campos como 'role' ficam fora e são definidos manualmente no controller.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Campos escondidos na serialização JSON.
     * Quando você fizer User::find(1)->toArray(), password e token não aparecem.
     *
     * Equivalente FastAPI: excluir campos no response_model/schema Pydantic.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts: converte tipos automaticamente ao ler/escrever do banco.
     *
     * 'password' => 'hashed': o Laravel faz Hash::make() automaticamente
     * ao definir $user->password = 'texto_puro'. Você nunca armazena
     * senha em texto plano — equivalente ao passlib.hash() no Python.
     *
     * 'email_verified_at' => 'datetime': converte string do banco → Carbon
     * (a lib de datas do PHP, equivalente ao datetime do Python).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Um usuário registra muitos pedidos.
     *
     * hasMany = "este usuário TEM MUITOS pedidos"
     * Equivalente SQLAlchemy: orders = relationship("Order", back_populates="user")
     *
     * O Laravel infere automaticamente que a FK é 'user_id' na tabela orders
     * (nome do model em snake_case + _id). Mesma convenção do SQLAlchemy.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
