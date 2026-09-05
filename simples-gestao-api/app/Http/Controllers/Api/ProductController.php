<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock');
        }

        $products = $query->latest('id')->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load('category');

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        $product->load('category');

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());
        $product->load('category');

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        if ($product->orderItems()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir produto vinculado a vendas anteriores. Considere desativá-lo.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $product->delete();

        return response()->noContent();
    }

    public function lowStock(): AnonymousResourceCollection
    {
        $products = Product::with('category')
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('is_active', true)
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return ProductResource::collection($products);
    }
}
