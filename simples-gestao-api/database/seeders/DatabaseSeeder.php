<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\FinancialCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Usuários ───────────────────────────────────────────────
        $admin = User::factory()->admin()->create([
            'name' => 'Admin SimplesGestão',
            'email' => 'admin@simplesgestao.com',
        ]);

        $operator = User::factory()->create([
            'name' => 'Maria Operadora',
            'email' => 'maria@simplesgestao.com',
        ]);

        // ─── Clientes ──────────────────────────────────────────────
        $customers = Customer::factory()->count(15)->create();

        // ─── Categorias de Produtos ─────────────────────────────────
        $categories = collect([
            'Bebidas'      => 'Refrigerantes, sucos, águas e cervejas',
            'Alimentos'    => 'Lanches, salgados e doces',
            'Eletrônicos'  => 'Cabos, carregadores e acessórios',
            'Higiene'      => 'Produtos de higiene pessoal',
            'Papelaria'    => 'Cadernos, canetas e materiais de escritório',
        ])->map(fn ($desc, $name) => Category::create([
            'name' => $name,
            'description' => $desc,
        ]));

        // ─── Produtos ───────────────────────────────────────────────
        $productsData = [
            ['Bebidas', 'Coca-Cola 350ml', 5.50, 3.30, 'BEB-0001', 50],
            ['Bebidas', 'Água Mineral 500ml', 3.00, 1.50, 'BEB-0002', 80],
            ['Bebidas', 'Suco de Laranja 1L', 8.90, 5.34, 'BEB-0003', 25],
            ['Alimentos', 'Coxinha', 6.00, 2.40, 'ALM-0001', 30],
            ['Alimentos', 'Pão de Queijo (6un)', 12.00, 6.00, 'ALM-0002', 20],
            ['Alimentos', 'Barra de Chocolate', 7.50, 4.50, 'ALM-0003', 40],
            ['Eletrônicos', 'Cabo USB-C', 25.00, 10.00, 'ELE-0001', 15],
            ['Eletrônicos', 'Fone de Ouvido P2', 35.00, 14.00, 'ELE-0002', 10],
            ['Higiene', 'Álcool em Gel 500ml', 12.00, 6.00, 'HIG-0001', 45],
            ['Higiene', 'Sabonete Líquido', 8.00, 3.20, 'HIG-0002', 35],
            ['Papelaria', 'Caderno 96 folhas', 15.00, 7.50, 'PAP-0001', 20],
            ['Papelaria', 'Caneta BIC Azul', 2.50, 1.00, 'PAP-0002', 100],
            ['Papelaria', 'Borracha Branca', 1.50, 0.60, 'PAP-0003', 3],
            ['Eletrônicos', 'Carregador iPhone', 45.00, 18.00, 'ELE-0003', 2],
        ];

        $products = collect();
        foreach ($productsData as [$catName, $name, $price, $cost, $sku, $stock]) {
            $category = $categories->firstWhere('name', $catName);
            $products->push(Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'price' => $price,
                'cost_price' => $cost,
                'sku' => $sku,
                'stock_quantity' => $stock,
                'min_stock' => 5,
                'is_active' => true,
            ]));
        }

        // ─── Categorias Financeiras ─────────────────────────────────
        $finCategories = collect([
            ['Vendas', 'income'],
            ['Serviços', 'income'],
            ['Aluguel', 'expense'],
            ['Salários', 'expense'],
            ['Fornecedores', 'expense'],
            ['Contas (luz/água/internet)', 'expense'],
            ['Material de Escritório', 'expense'],
        ])->map(fn ($data) => FinancialCategory::create([
            'name' => $data[0],
            'type' => $data[1],
        ]));

        $salesCategory = $finCategories->firstWhere('name', 'Vendas');

        // ─── Pedidos com Itens ──────────────────────────────────────
        for ($i = 0; $i < 20; $i++) {
            $order = Order::create([
                'customer_id' => $customers->random()->id,
                'user_id' => fake()->randomElement([$admin->id, $operator->id]),
                'order_number' => 'PED-' . now()->subDays(rand(0, 60))->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'status' => fake()->randomElement(['pending', 'confirmed', 'delivered', 'confirmed', 'delivered']),
                'payment_method' => fake()->randomElement(['cash', 'pix', 'credit_card', 'debit_card']),
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
            ]);

            $orderProducts = $products->random(rand(1, 4));
            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $qty,
                ]);
            }

            $order->recalculateTotals();

            // Pedidos confirmados/entregues geram transação de entrada
            if (in_array($order->status, ['confirmed', 'delivered'])) {
                Transaction::create([
                    'financial_category_id' => $salesCategory->id,
                    'order_id' => $order->id,
                    'type' => 'income',
                    'amount' => $order->total,
                    'description' => "Venda #{$order->order_number}",
                    'transaction_date' => $order->created_at->format('Y-m-d'),
                ]);
            }
        }

        // ─── Transações manuais (despesas) ──────────────────────────
        $expenseCategories = $finCategories->where('type', 'expense');

        for ($i = 0; $i < 15; $i++) {
            $finCat = $expenseCategories->random();
            Transaction::create([
                'financial_category_id' => $finCat->id,
                'type' => 'expense',
                'amount' => fake()->randomFloat(2, 100, 3000),
                'description' => $finCat->name . ' - ' . fake()->monthName(),
                'transaction_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            ]);
        }

        $this->command->info('');
        $this->command->info('🌱 Seed concluído!');
        $this->command->info("   👤 Usuários: 2 (admin + operador)");
        $this->command->info("   👥 Clientes: {$customers->count()}");
        $this->command->info("   📦 Produtos: {$products->count()}");
        $this->command->info("   🛒 Pedidos: 20 (com itens)");
        $this->command->info("   💰 Transações: " . Transaction::count());
        $this->command->info('');
        $this->command->info('   Login: admin@simplesgestao.com / password');
    }
}
