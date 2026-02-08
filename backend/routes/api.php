<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController; // ✅ เพิ่มบรรทัดนี้

Route::options('/{any}', function () {
    return response()->noContent();
})->where('any', '.*');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::post('/checkout', [CheckoutController::class, 'create']);

Route::get('/payments/{intentId}', [PaymentController::class, 'show']);
Route::post('/payments/{intentId}/confirm', [PaymentController::class, 'confirm']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        Route::get('/admin-users', [AdminUserController::class, 'index']);
        Route::post('/admin-users', [AdminUserController::class, 'store']);
        Route::delete('/admin-users/{id}', [AdminUserController::class, 'destroy']);

        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::patch('/products/{id}/toggle', [AdminProductController::class, 'toggle']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{id}/ship', [AdminOrderController::class, 'ship']);
    });
});

// ✅ Customer auth ต้องอยู่นอก admin
Route::prefix('auth')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'customer'])->group(function () {
        Route::get('/me', [CustomerAuthController::class, 'me']);
        Route::post('/logout', [CustomerAuthController::class, 'logout']);

        // ✅ เพิ่ม 2 routes นี้
        Route::get('/orders', [CustomerOrderController::class, 'index']);
        Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'show']);
    });
});
