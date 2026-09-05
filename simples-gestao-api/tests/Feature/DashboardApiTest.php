<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\FinancialCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_dashboard_summary_metrics(): void
    {
        $customers = Customer::factory()->count(5)->create();

        Product::factory()->create([
            'stock_quantity' => 2,
            'min_stock' => 10,
        ]);

        Transaction::factory()->create([
            'type' => 'income',
            'amount' => 5000.00,
            'transaction_date' => now()->toDateString(),
        ]);

        Transaction::factory()->create([
            'type' => 'expense',
            'amount' => 2000.00,
            'transaction_date' => now()->toDateString(),
        ]);

        Order::factory()->create([
            'customer_id' => $customers->first()->id,
            'user_id' => $this->user->id,
            'status' => 'confirmed',
            'total' => 5000.00,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/dashboard/summary?period=this_month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'period' => ['start_date', 'end_date'],
                'metrics' => [
                    'total_income',
                    'total_expense',
                    'net_balance',
                    'confirmed_orders_count',
                    'pending_orders_count',
                    'average_ticket',
                    'low_stock_count',
                    'total_customers',
                ],
            ])
            ->assertJsonPath('metrics.total_income', 5000)
            ->assertJsonPath('metrics.total_expense', 2000)
            ->assertJsonPath('metrics.net_balance', 3000)
            ->assertJsonPath('metrics.low_stock_count', 1)
            ->assertJsonPath('metrics.total_customers', 5);
    }

    public function test_can_get_dashboard_charts_data(): void
    {
        $cat = FinancialCategory::factory()->create(['type' => 'expense', 'name' => 'Luz']);

        Transaction::factory()->create([
            'financial_category_id' => $cat->id,
            'type' => 'expense',
            'amount' => 300.00,
            'transaction_date' => now()->toDateString(),
        ]);

        $order = Order::factory()->create([
            'status' => 'confirmed',
        ]);

        $product = Product::factory()->create(['name' => 'Produto Top']);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 15.00,
            'subtotal' => 150.00,
        ]);

        $response = $this->getJson('/api/dashboard/charts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cash_flow' => [
                    '*' => ['month_key', 'label', 'income', 'expense', 'balance'],
                ],
                'expenses_by_category' => [
                    '*' => ['category_id', 'name', 'amount', 'percentage'],
                ],
                'top_selling_products' => [
                    '*' => ['id', 'name', 'sku', 'total_quantity', 'total_revenue'],
                ],
                'recent_orders',
            ])
            ->assertJsonPath('top_selling_products.0.name', 'Produto Top')
            ->assertJsonPath('top_selling_products.0.total_quantity', 10);
    }
}
