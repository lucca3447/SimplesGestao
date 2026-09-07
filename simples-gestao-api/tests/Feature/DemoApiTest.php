<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reset_demo_database(): void
    {
        // Cria registros arbitrários antes
        Customer::factory()->count(2)->create();
        Product::factory()->count(2)->create();

        $response = $this->postJson('/api/demo/reset');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        // Após o reset, o seeder padrão deve ter repopulado o banco
        $this->assertDatabaseHas('users', [
            'email' => 'admin@simplesgestao.com',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'maria@simplesgestao.com',
        ]);
    }
}
