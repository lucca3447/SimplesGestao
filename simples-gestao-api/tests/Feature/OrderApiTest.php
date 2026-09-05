<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_create_pending_order_with_items(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'payment_method' => 'pix',
            'discount' => 10.00,
            'status' => 'pending',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.subtotal', 100)
            ->assertJsonPath('data.discount', 10)
            ->assertJsonPath('data.total', 90);

        // Estoque não deve ter sido alterado em status pending
        $this->assertEquals(10, $product->fresh()->stock_quantity);

        // Nenhuma transação financeira criada para pedido pendente
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_can_confirm_order_and_deduct_stock_and_create_income_transaction(): void
    {
        $product = Product::factory()->create([
            'price' => 30.00,
            'stock_quantity' => 15,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'subtotal' => 60.00,
            'discount' => 0,
            'total' => 60.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 30.00,
            'subtotal' => 60.00,
        ]);

        $response = $this->patchJson("/api/orders/{$order->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');

        // Estoque decrementado de 15 para 13
        $this->assertEquals(13, $product->fresh()->stock_quantity);

        // Transação financeira de entrada criada
        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'type' => 'income',
            'amount' => 60.00,
        ]);
    }

    public function test_cannot_confirm_order_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 1,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 20.00,
            'subtotal' => 100.00,
        ]);

        $response = $this->patchJson("/api/orders/{$order->id}/confirm");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        // Status permanece pending e estoque inalterado
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    public function test_cancelling_confirmed_order_restores_stock_and_deletes_transaction(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 8,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'confirmed',
            'total' => 100.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        Transaction::factory()->create([
            'order_id' => $order->id,
            'type' => 'income',
            'amount' => 100.00,
        ]);

        $response = $this->patchJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        // Estoque estornado de 8 para 10
        $this->assertEquals(10, $product->fresh()->stock_quantity);

        // Transação financeira estornada/removida
        $this->assertDatabaseMissing('transactions', [
            'order_id' => $order->id,
        ]);
    }
}
