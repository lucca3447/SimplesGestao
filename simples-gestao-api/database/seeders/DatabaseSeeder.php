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
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Usuários de Demonstração (sem depender de factories/faker) ──
        $admin = User::firstOrCreate(
            ['email' => 'admin@simplesgestao.com'],
            [
                'name' => 'Admin SimplesGestão',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'maria@simplesgestao.com'],
            [
                'name' => 'Maria Operadora',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // ─── Clientes Realistas (sem depender de faker em produção) ────
        $customersData = [
            ['João Silva', 'joao.silva@email.com', '(11) 98765-4321', '123.456.789-01', 'Av. Paulista, 1000 - Bela Vista, São Paulo - SP'],
            ['Maria Santos', 'maria.santos@email.com', '(21) 97654-3210', '234.567.890-12', 'Rua Visconde de Pirajá, 500 - Ipanema, Rio de Janeiro - RJ'],
            ['Carlos Eduardo Oliveira', 'carlos.oliveira@email.com', '(31) 96543-2109', '345.678.901-23', 'Av. Afonso Pena, 1500 - Centro, Belo Horizonte - MG'],
            ['Ana Paula Souza', 'ana.souza@email.com', '(41) 95432-1098', '456.789.012-34', 'Rua XV de Novembro, 800 - Centro, Curitiba - PR'],
            ['Lucas Ferreira', 'lucas.ferreira@email.com', '(51) 94321-0987', '567.890.123-45', 'Rua dos Andradas, 1200 - Centro, Porto Alegre - RS'],
            ['Beatriz Lima', 'beatriz.lima@email.com', '(71) 93210-9876', '678.901.234-56', 'Av. Oceânica, 400 - Barra, Salvador - BA'],
            ['Rodrigo Almeida', 'rodrigo.almeida@email.com', '(85) 92109-8765', '789.012.345-67', 'Av. Beira Mar, 2000 - Meireles, Fortaleza - CE'],
            ['Juliana Costa', 'juliana.costa@email.com', '(61) 91098-7654', '890.123.456-78', 'SQS 305 Bloco B - Asa Sul, Brasília - DF'],
            ['Felipe Rocha', 'felipe.rocha@email.com', '(81) 99876-5432', '901.234.567-89', 'Av. Boa Viagem, 3500 - Boa Viagem, Recife - PE'],
            ['Camila Ribeiro', 'camila.ribeiro@email.com', '(92) 98765-1234', '012.345.678-90', 'Av. Djalma Batista, 1600 - Chapada, Manaus - AM'],
            ['Gabriel Martins', 'gabriel.martins@email.com', '(19) 97654-2345', '135.792.468-01', 'Av. Francisco Glicério, 1100 - Centro, Campinas - SP'],
            ['Larissa Barbosa', 'larissa.barbosa@email.com', '(48) 96543-3456', '246.801.357-12', 'Rua Felipe Schmidt, 600 - Centro, Florianópolis - SC'],
            ['Thiago Mendes', 'thiago.mendes@email.com', '(27) 95432-4567', '357.913.579-23', 'Av. Dante Michelini, 900 - Jardim da Penha, Vitória - ES'],
            ['Fernanda Dias', 'fernanda.dias@email.com', '(62) 94321-5678', '468.024.680-34', 'Av. 85, 1200 - Setor Sul, Goiânia - GO'],
            ['Bruno Carvalho', 'bruno.carvalho@email.com', '(84) 93210-6789', '579.135.791-45', 'Av. Eng. Roberto Freire, 2200 - Ponta Negra, Natal - RN'],
        ];

        $customers = collect();
        foreach ($customersData as $c) {
            $customers->push(Customer::firstOrCreate(
                ['email' => $c[1]],
                [
                    'name' => $c[0],
                    'phone' => $c[2],
                    'cpf_cnpj' => $c[3],
                    'address' => $c[4],
                ]
            ));
        }

        // ─── Categorias de Produtos ─────────────────────────────────
        $categoriesMap = [
            'Bebidas'      => 'Refrigerantes, sucos, águas e cervejas',
            'Alimentos'    => 'Lanches, salgados e doces',
            'Eletrônicos'  => 'Cabos, carregadores e acessórios',
            'Higiene'      => 'Produtos de higiene pessoal',
            'Papelaria'    => 'Cadernos, canetas e materiais de escritório',
        ];

        $categories = collect();
        foreach ($categoriesMap as $name => $desc) {
            $categories->push(Category::firstOrCreate(
                ['name' => $name],
                ['description' => $desc]
            ));
        }

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
            $products->push(Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'cost_price' => $cost,
                    'stock_quantity' => $stock,
                    'min_stock' => 5,
                    'is_active' => true,
                ]
            ));
        }

        // ─── Categorias Financeiras ─────────────────────────────────
        $finData = [
            ['Vendas', 'income'],
            ['Serviços', 'income'],
            ['Aluguel', 'expense'],
            ['Salários', 'expense'],
            ['Fornecedores', 'expense'],
            ['Contas (luz/água/internet)', 'expense'],
            ['Material de Escritório', 'expense'],
        ];

        $finCategories = collect();
        foreach ($finData as [$name, $type]) {
            $finCategories->push(FinancialCategory::firstOrCreate(
                ['name' => $name],
                ['type' => $type]
            ));
        }

        $salesCategory = $finCategories->firstWhere('name', 'Vendas');

        // ─── Pedidos com Itens (se não existirem) ───────────────────
        if (Order::count() === 0) {
            $statuses = ['confirmed', 'delivered', 'pending', 'confirmed', 'delivered'];
            $paymentMethods = ['pix', 'credit_card', 'cash', 'debit_card'];

            for ($i = 0; $i < 20; $i++) {
                $status = $statuses[$i % count($statuses)];
                $payment = $paymentMethods[$i % count($paymentMethods)];
                $daysAgo = ($i * 2) % 60;
                $createdAt = now()->subDays($daysAgo)->setHour(10 + ($i % 8))->setMinute(15 + ($i * 2) % 40);

                $order = Order::create([
                    'customer_id' => $customers[$i % $customers->count()]->id,
                    'user_id' => ($i % 2 === 0) ? $admin->id : $operator->id,
                    'order_number' => 'PED-' . $createdAt->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'payment_method' => $payment,
                    'subtotal' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Itens do pedido (1 a 3 produtos por pedido)
                $itemsCount = ($i % 3) + 1;
                for ($j = 0; $j < $itemsCount; $j++) {
                    $prod = $products[($i + $j) % $products->count()];
                    $qty = ($j % 2) + 1;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $prod->id,
                        'quantity' => $qty,
                        'unit_price' => $prod->price,
                        'subtotal' => $prod->price * $qty,
                    ]);
                }

                $order->recalculateTotals();

                // Pedidos confirmados/entregues geram transação financeira de entrada
                if (in_array($order->status, ['confirmed', 'delivered'])) {
                    Transaction::create([
                        'financial_category_id' => $salesCategory->id,
                        'order_id' => $order->id,
                        'type' => 'income',
                        'amount' => $order->total,
                        'description' => "Venda #{$order->order_number}",
                        'transaction_date' => $createdAt->format('Y-m-d'),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        // ─── Transações Manuais de Despesas ─────────────────────────
        $expenseCategories = $finCategories->where('type', 'expense')->values();

        if (Transaction::where('type', 'expense')->count() === 0) {
            for ($i = 0; $i < 15; $i++) {
                $finCat = $expenseCategories[$i % $expenseCategories->count()];
                $daysAgo = ($i * 3) + 1;
                $date = now()->subDays($daysAgo);
                $amount = round(120.50 + ($i * 85.30), 2);

                Transaction::create([
                    'financial_category_id' => $finCat->id,
                    'type' => 'expense',
                    'amount' => $amount,
                    'description' => "{$finCat->name} - Mês anterior",
                    'transaction_date' => $date->format('Y-m-d'),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('🌱 Seed concluído com sucesso!');
        $this->command->info("   👤 Usuários: 2 (admin + operador)");
        $this->command->info("   👥 Clientes: {$customers->count()}");
        $this->command->info("   📦 Produtos: {$products->count()}");
        $this->command->info("   🛒 Pedidos: " . Order::count());
        $this->command->info("   💰 Transações: " . Transaction::count());
        $this->command->info('');
        $this->command->info('   Login: admin@simplesgestao.com / password');
    }
}
