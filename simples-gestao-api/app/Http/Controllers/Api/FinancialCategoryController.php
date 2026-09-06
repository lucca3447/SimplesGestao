<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialCategory\StoreFinancialCategoryRequest;
use App\Http\Requests\FinancialCategory\UpdateFinancialCategoryRequest;
use App\Http\Resources\FinancialCategoryResource;
use App\Models\FinancialCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FinancialCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = FinancialCategory::withCount('transactions');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%']);
        }

        if ($request->boolean('all')) {
            return FinancialCategoryResource::collection($query->orderBy('name')->get());
        }

        $categories = $query->latest('id')->paginate($request->integer('per_page', 15));

        return FinancialCategoryResource::collection($categories);
    }

    public function store(StoreFinancialCategoryRequest $request): JsonResponse
    {
        $category = FinancialCategory::create($request->validated());

        return (new FinancialCategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(FinancialCategory $financialCategory): FinancialCategoryResource
    {
        $financialCategory->loadCount('transactions');

        return new FinancialCategoryResource($financialCategory);
    }

    public function update(UpdateFinancialCategoryRequest $request, FinancialCategory $financialCategory): FinancialCategoryResource
    {
        $financialCategory->update($request->validated());
        $financialCategory->loadCount('transactions');

        return new FinancialCategoryResource($financialCategory);
    }

    public function destroy(FinancialCategory $financialCategory): Response
    {
        if ($financialCategory->transactions()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir categoria financeira com transações vinculadas.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $financialCategory->delete();

        return response()->noContent();
    }
}
