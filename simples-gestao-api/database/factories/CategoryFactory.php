<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Bebidas', 'Alimentos', 'Eletrônicos', 'Vestuário',
                'Higiene', 'Limpeza', 'Papelaria', 'Ferramentas',
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
