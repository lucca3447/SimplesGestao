<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Transaction::with('financialCategory');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->input('financial_category_id')) {
            $query->where('financial_category_id', $categoryId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'ilike', "%{$search}%");
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = Transaction::create($request->validated());
        $transaction->load('financialCategory');

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        $transaction->load(['financialCategory', 'order']);

        return new TransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        if ($transaction->order_id !== null) {
            throw ValidationException::withMessages([
                'transaction' => ['Transações vinculadas a pedidos são sincronizadas diretamente pelo pedido.'],
            ]);
        }

        $transaction->update($request->validated());
        $transaction->load('financialCategory');

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction): Response
    {
        if ($transaction->order_id !== null) {
            throw ValidationException::withMessages([
                'transaction' => ['Transações de vendas não podem ser excluídas manualmente. Cancele o pedido associado.'],
            ]);
        }

        $transaction->delete();

        return response()->noContent();
    }
}
