<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FinancialCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class DemoController extends Controller
{
    /**
     * Restaura o banco de dados para o estado inicial de demonstração.
     */
    public function reset(): JsonResponse
    {
        if (config('app.allow_demo_reset', true) === false) {
            return response()->json([
                'message' => 'Restauração de demonstração desabilitada neste ambiente.',
            ], Response::HTTP_FORBIDDEN);
        }

        Schema::disableForeignKeyConstraints();

        OrderItem::truncate();
        Order::truncate();
        Transaction::truncate();
        FinancialCategory::truncate();
        Product::truncate();
        Category::truncate();
        Customer::truncate();
        PersonalAccessToken::truncate();
        User::truncate();

        Schema::enableForeignKeyConstraints();

        Artisan::call('db:seed', [
            '--class' => 'DatabaseSeeder',
            '--force' => true,
        ]);

        return response()->json([
            'message' => 'Banco de dados de demonstração restaurado com sucesso para o estado original.',
        ]);
    }
}
