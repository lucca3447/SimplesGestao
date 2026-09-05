<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Laravel\Sanctum\Sanctum::actingAs(\App\Models\User::factory()->create());
    }

    public function test_can_list_customers(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'cpf_cnpj', 'address', 'notes'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_customer(): void
    {
        $payload = [
            'name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'phone' => '11999999999',
            'cpf_cnpj' => '123.456.789-00',
            'address' => 'Rua Teste, 123',
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Cliente Teste')
            ->assertJsonPath('data.email', 'cliente@teste.com');

        $this->assertDatabaseHas('customers', [
            'email' => 'cliente@teste.com',
        ]);
    }

    public function test_validates_required_name_on_create(): void
    {
        $response = $this->postJson('/api/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Nome Antigo',
        ]);

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'Nome Atualizado',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Atualizado');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
