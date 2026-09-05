<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create());
    }

    public function test_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description'],
                ],
            ]);
    }

    public function test_can_create_category(): void
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Bebidas Especiais',
            'description' => 'Sucos naturais e artesanais',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Bebidas Especiais');

        $this->assertDatabaseHas('categories', [
            'name' => 'Bebidas Especiais',
        ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
