<?php

namespace Tests\Feature;

use App\Models\FinancialCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinancialCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_financial_categories(): void
    {
        FinancialCategory::factory()->create(['name' => 'Vendas Balcão', 'type' => 'income']);
        FinancialCategory::factory()->create(['name' => 'Aluguel Predial', 'type' => 'expense']);

        $response = $this->getJson('/api/financial-categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'type', 'transactions_count', 'created_at'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_financial_categories_by_type(): void
    {
        FinancialCategory::factory()->create(['name' => 'Receita Extra', 'type' => 'income']);
        FinancialCategory::factory()->create(['name' => 'Energia Elétrica', 'type' => 'expense']);

        $response = $this->getJson('/api/financial-categories?type=income');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Receita Extra');
    }

    public function test_can_search_financial_categories_case_insensitive(): void
    {
        FinancialCategory::factory()->create(['name' => 'Serviços de TI', 'type' => 'income']);
        FinancialCategory::factory()->create(['name' => 'Material de Escritório', 'type' => 'expense']);

        $response = $this->getJson('/api/financial-categories?search=serviços');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Serviços de TI');
    }

    public function test_can_create_financial_category(): void
    {
        $payload = [
            'name' => 'Assinaturas de Software',
            'type' => 'expense',
        ];

        $response = $this->postJson('/api/financial-categories', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Assinaturas de Software')
            ->assertJsonPath('data.type', 'expense');

        $this->assertDatabaseHas('financial_categories', [
            'name' => 'Assinaturas de Software',
            'type' => 'expense',
        ]);
    }

    public function test_can_show_financial_category(): void
    {
        $category = FinancialCategory::factory()->create(['name' => 'Consultoria', 'type' => 'income']);

        $response = $this->getJson("/api/financial-categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', 'Consultoria');
    }

    public function test_can_update_financial_category(): void
    {
        $category = FinancialCategory::factory()->create(['name' => 'Nome Velho', 'type' => 'income']);

        $response = $this->putJson("/api/financial-categories/{$category->id}", [
            'name' => 'Nome Novo',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Novo');

        $this->assertDatabaseHas('financial_categories', [
            'id' => $category->id,
            'name' => 'Nome Novo',
        ]);
    }

    public function test_cannot_change_type_of_category_with_transactions(): void
    {
        $category = FinancialCategory::factory()->create(['name' => 'Vendas', 'type' => 'income']);

        Transaction::factory()->create([
            'financial_category_id' => $category->id,
            'type' => 'income',
        ]);

        $response = $this->putJson("/api/financial-categories/{$category->id}", [
            'name' => 'Vendas Modificadas',
            'type' => 'expense', // Tentativa inválida de mutação de tipo
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_can_delete_financial_category_without_transactions(): void
    {
        $category = FinancialCategory::factory()->create();

        $response = $this->deleteJson("/api/financial-categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('financial_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_financial_category_with_transactions(): void
    {
        $category = FinancialCategory::factory()->create(['type' => 'expense']);

        Transaction::factory()->create([
            'financial_category_id' => $category->id,
            'type' => 'expense',
        ]);

        $response = $this->deleteJson("/api/financial-categories/{$category->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('financial_categories', [
            'id' => $category->id,
        ]);
    }
}
