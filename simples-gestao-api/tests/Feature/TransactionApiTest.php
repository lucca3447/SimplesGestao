<?php

namespace Tests\Feature;

use App\Models\FinancialCategory;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_transactions(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'amount', 'type', 'description', 'transaction_date'],
                ],
            ]);
    }

    public function test_can_create_manual_expense_transaction(): void
    {
        $category = FinancialCategory::factory()->create(['type' => 'expense']);

        $payload = [
            'financial_category_id' => $category->id,
            'type' => 'expense',
            'amount' => 1500.00,
            'description' => 'Aluguel do imóvel',
            'transaction_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/transactions', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.amount', 1500)
            ->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.description', 'Aluguel do imóvel');

        $this->assertDatabaseHas('transactions', [
            'description' => 'Aluguel do imóvel',
            'amount' => 1500.00,
        ]);
    }

    public function test_cannot_delete_order_linked_transaction(): void
    {
        $order = Order::factory()->create();
        $transaction = Transaction::factory()->create([
            'order_id' => $order->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }
}
