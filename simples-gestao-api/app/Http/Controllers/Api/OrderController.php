<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\FinancialCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::with(['customer', 'user'])->withCount('items');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->latest('id')->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $unitPrice = $item['unit_price'] ?? $product->price;
                $lineSubtotal = round($unitPrice * $item['quantity'], 2);
                $subtotal += $lineSubtotal;

                $itemsData[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = $validated['discount'] ?? 0;
            $total = max(0, $subtotal - $discount);
            $initialStatus = $validated['status'] ?? 'pending';

            if ($initialStatus === 'confirmed') {
                foreach ($itemsData as $data) {
                    if ($data['product']->stock_quantity < $data['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => ["Estoque insuficiente para {$data['product']->name}. Disponível: {$data['product']->stock_quantity}, Solicitado: {$data['quantity']}"],
                        ]);
                    }
                }
            }

            $order = Order::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => $initialStatus,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'unit_price' => $data['unit_price'],
                    'subtotal' => $data['subtotal'],
                ]);

                if ($initialStatus === 'confirmed') {
                    $data['product']->decrement('stock_quantity', $data['quantity']);
                }
            }

            if ($initialStatus === 'confirmed') {
                $salesCategory = FinancialCategory::firstOrCreate(
                    ['name' => 'Vendas', 'type' => 'income']
                );

                Transaction::create([
                    'financial_category_id' => $salesCategory->id,
                    'order_id' => $order->id,
                    'type' => 'income',
                    'amount' => $order->total,
                    'description' => "Venda #{$order->order_number}",
                    'transaction_date' => now()->toDateString(),
                ]);
            }

            return $order;
        });

        $order->load(['customer', 'user', 'items.product']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'user', 'items.product']);

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'order' => ['Não é possível alterar pedidos cancelados.'],
            ]);
        }

        $validated = $request->validated();

        if (array_key_exists('discount', $validated)) {
            $order->discount = $validated['discount'];
            $order->total = max(0, $order->subtotal - $order->discount);
        }

        if (array_key_exists('payment_method', $validated)) {
            $order->payment_method = $validated['payment_method'];
        }

        if (array_key_exists('notes', $validated)) {
            $order->notes = $validated['notes'];
        }

        $order->save();

        // Atualiza valor da transação financeira se o total mudou
        if ($order->status === 'confirmed') {
            $order->transactions()->where('type', 'income')->update([
                'amount' => $order->total,
            ]);
        }

        $order->load(['customer', 'user', 'items.product']);

        return new OrderResource($order);
    }

    public function confirm(Order $order): OrderResource
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ["Apenas pedidos pendentes podem ser confirmados. Status atual: {$order->status}"],
            ]);
        }

        DB::transaction(function () use ($order) {
            $order->load('items.product');

            foreach ($order->items as $item) {
                if ($item->product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Estoque insuficiente para {$item->product->name}. Disponível: {$item->product->stock_quantity}, Solicitado: {$item->quantity}"],
                    ]);
                }
            }

            foreach ($order->items as $item) {
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'confirmed']);

            $salesCategory = FinancialCategory::firstOrCreate(
                ['name' => 'Vendas', 'type' => 'income']
            );

            Transaction::create([
                'financial_category_id' => $salesCategory->id,
                'order_id' => $order->id,
                'type' => 'income',
                'amount' => $order->total,
                'description' => "Venda #{$order->order_number}",
                'transaction_date' => now()->toDateString(),
            ]);
        });

        $order->load(['customer', 'user', 'items.product']);

        return new OrderResource($order);
    }

    public function cancel(Order $order): OrderResource
    {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => ['Este pedido já está cancelado.'],
            ]);
        }

        DB::transaction(function () use ($order) {
            $order->load('items.product');

            if (in_array($order->status, ['confirmed', 'delivered'])) {
                foreach ($order->items as $item) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }

                $order->transactions()->delete();
            }

            $order->update(['status' => 'cancelled']);
        });

        $order->load(['customer', 'user', 'items.product']);

        return new OrderResource($order);
    }

    public function destroy(Order $order): Response
    {
        if ($order->status !== 'cancelled') {
            return response()->json([
                'message' => 'Apenas pedidos cancelados podem ser excluídos do sistema.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->delete();

        return response()->noContent();
    }
}
