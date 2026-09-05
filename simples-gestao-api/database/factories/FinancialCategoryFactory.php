<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialCategoryFactory extends Factory
{
    protected $model = FinancialCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement(['income', 'expense']),
        ];
    }
}
