<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinancialCategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// Rotas públicas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas protegidas por token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('categories', CategoryController::class);

    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class);

    Route::patch('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::patch('orders/{order}/deliver', [OrderController::class, 'deliver']);
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::apiResource('orders', OrderController::class);

    Route::apiResource('financial-categories', FinancialCategoryController::class);
    Route::apiResource('transactions', TransactionController::class);

    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('dashboard/charts', [DashboardController::class, 'charts']);
});
