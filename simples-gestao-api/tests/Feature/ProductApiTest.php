<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_with_category(): void
    {
        Product::factory()->count(2)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'price', 'stock_quantity',
                        'min_stock', 'is_low_stock', 'category',
                    ],
                ],
            ]);
    }

    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'name' => 'Café Especial 250g',
            'sku' => 'CAF-0001',
            'price' => 28.50,
            'cost_price' => 14.00,
            'stock_quantity' => 20,
            'min_stock' => 5,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Café Especial 250g')
            ->assertJsonPath('data.sku', 'CAF-0001');

        $this->assertDatabaseHas('products', [
            'sku' => 'CAF-0001',
        ]);
    }

    public function test_can_filter_low_stock_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produto Normal',
            'stock_quantity' => 50,
            'min_stock' => 10,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Produto Crítico',
            'stock_quantity' => 2,
            'min_stock' => 10,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Produto Crítico');
    }
}
