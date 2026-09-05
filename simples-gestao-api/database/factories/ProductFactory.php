<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 500);

        return [
            // Se não passar category_id, cria uma Category automaticamente
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),  // 3 palavras juntas como string
            'description' => fake()->sentence(),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')), // Ex: ABC-1234
            'price' => $price,
            'cost_price' => round($price * 0.6, 2),  // custo = 60% do preço (margem 40%)
            'stock_quantity' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(5, 15),
            'is_active' => true,
        ];
    }

    /**
     * Produto com estoque zerado (pra testar alertas).
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'min_stock' => 10,
        ]);
    }
}
