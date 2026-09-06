<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $incomeQuery = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $expenseQuery = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $totalIncome = (float) ($incomeQuery->sum('amount') ?? 0);
        $totalExpense = (float) ($expenseQuery->sum('amount') ?? 0);
        $netBalance = round($totalIncome - $totalExpense, 2);

        $confirmedOrdersCount = Order::whereIn('status', ['confirmed', 'delivered'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->count();

        $pendingOrdersCount = Order::where('status', 'pending')->count();

        $averageTicket = $confirmedOrdersCount > 0
            ? round($totalIncome / $confirmedOrdersCount, 2)
            : 0;

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();

        $totalCustomers = Customer::count();

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'metrics' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_balance' => $netBalance,
                'confirmed_orders_count' => $confirmedOrdersCount,
                'pending_orders_count' => $pendingOrdersCount,
                'average_ticket' => $averageTicket,
                'low_stock_count' => $lowStockCount,
                'total_customers' => $totalCustomers,
            ],
        ]);
    }

    public function charts(Request $request): JsonResponse
    {
        $monthsCount = min(12, max(3, $request->integer('months', 6)));
        $cashFlow = [];

        $earliestMonth = Carbon::now()->subMonths($monthsCount - 1)->startOfMonth()->toDateString();
        $latestMonth = Carbon::now()->endOfMonth()->toDateString();

        // Carrega todas as transações do período em uma única consulta rápida
        $periodTransactions = Transaction::whereBetween('transaction_date', [$earliestMonth, $latestMonth])
            ->select('type', 'amount', 'transaction_date')
            ->get()
            ->groupBy(fn ($t) => substr((string) $t->transaction_date, 0, 7));

        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');
            $monthItems = $periodTransactions->get($monthKey, collect());

            $income = (float) $monthItems->where('type', 'income')->sum('amount');
            $expense = (float) $monthItems->where('type', 'expense')->sum('amount');

            $cashFlow[] = [
                'month_key' => $monthKey,
                'label' => $monthDate->translatedFormat('M/y'),
                'income' => round($income, 2),
                'expense' => round($expense, 2),
                'balance' => round($income - $expense, 2),
            ];
        }

        // Despesas agrupadas por categoria financeira nos últimos 90 dias
        $ninetyDaysAgo = Carbon::now()->subDays(90)->toDateString();
        $expensesByCategory = Transaction::where('transactions.type', 'expense')
            ->where('transaction_date', '>=', $ninetyDaysAgo)
            ->join('financial_categories', 'transactions.financial_category_id', '=', 'financial_categories.id')
            ->select(
                'financial_categories.id',
                'financial_categories.name',
                DB::raw('SUM(transactions.amount) as total_amount')
            )
            ->groupBy('financial_categories.id', 'financial_categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $totalCategorizedExpenses = $expensesByCategory->sum('total_amount');

        $expensesBreakdown = $expensesByCategory->map(function ($item) use ($totalCategorizedExpenses) {
            $amount = (float) $item->total_amount;
            $percentage = $totalCategorizedExpenses > 0
                ? round(($amount / $totalCategorizedExpenses) * 100, 1)
                : 0;

            return [
                'category_id' => $item->id,
                'name' => $item->name,
                'amount' => round($amount, 2),
                'percentage' => $percentage,
            ];
        });

        // Top 5 produtos mais vendidos
        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['confirmed', 'delivered'])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'total_quantity' => (int) $p->total_quantity,
                'total_revenue' => (float) round($p->total_revenue, 2),
            ]);

        // Últimos 5 pedidos
        $recentOrders = Order::with('customer')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer?->name ?? 'Consumidor Final',
                'status' => $o->status,
                'total' => (float) $o->total,
                'created_at' => $o->created_at?->toISOString(),
            ]);

        return response()->json([
            'cash_flow' => $cashFlow,
            'expenses_by_category' => $expensesBreakdown,
            'top_selling_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start = Carbon::parse($request->input('start_date'))->toDateString();
                $end = Carbon::parse($request->input('end_date'))->toDateString();
                if ($start <= $end) {
                    return [$start, $end];
                }
            } catch (\Exception) {
                // Em caso de formato inválido, recorre ao período padrão abaixo
            }
        }

        $period = $request->input('period', 'this_month');

        return match ($period) {
            'today' => [
                Carbon::today()->toDateString(),
                Carbon::today()->toDateString(),
            ],
            'last_30_days' => [
                Carbon::now()->subDays(30)->toDateString(),
                Carbon::now()->toDateString(),
            ],
            'last_month' => [
                Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                Carbon::now()->subMonth()->endOfMonth()->toDateString(),
            ],
            'this_year' => [
                Carbon::now()->startOfYear()->toDateString(),
                Carbon::now()->toDateString(),
            ],
            default => [ // this_month
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ],
        };
    }
}
