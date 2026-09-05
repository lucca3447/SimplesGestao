<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Customer.
 *
 * Factories no Laravel = fixtures/factories no pytest.
 * Geram dados fake realistas usando a lib Faker (embutida no Laravel).
 *
 * Uso:
 *   Customer::factory()->create()           → cria 1 e salva no banco
 *   Customer::factory()->count(10)->create() → cria 10
 *   Customer::factory()->make()              → cria sem salvar (pra testes)
 *
 * Equivalente Python/pytest:
 *   @pytest.fixture
 *   def customer(db_session):
 *       return CustomerFactory.create()
 *
 * fake('pt_BR') usa o locale que configuramos no .env (APP_FAKER_LOCALE).
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'cpf_cnpj' => fake()->unique()->cpf(),   // Faker pt_BR gera CPF válido!
            'address' => fake()->address(),
            'notes' => fake()->optional(0.3)->sentence(), // 30% chance de ter nota
        ];
    }
}
