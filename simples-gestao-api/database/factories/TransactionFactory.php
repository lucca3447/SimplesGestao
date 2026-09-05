<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'financial_category_id' => FinancialCategory::factory(),
            'order_id' => null,
            'type' => $type,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'description' => $type === 'income'
                ? fake()->randomElement(['Venda de produtos', 'Serviço prestado', 'Consultoria'])
                : fake()->randomElement(['Aluguel', 'Conta de luz', 'Salário', 'Fornecedor', 'Material']),
            'transaction_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
