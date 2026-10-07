<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);
        Route::post('/suppliers/{supplier}/deactivate', [SupplierController::class, 'deactivate']);
        Route::post('/suppliers/{supplier}/reactivate', [SupplierController::class, 'reactivate']);

        Route::get('/purchase-orders/products', [PurchaseOrderController::class, 'products']);
        Route::get('/purchase-orders/suppliers', [PurchaseOrderController::class, 'suppliers']);
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::put('/purchase-orders/{purchaseOrder}/message', [PurchaseOrderController::class, 'updateMessage']);
        Route::put('/purchase-orders/{purchaseOrder}/draft', [PurchaseOrderController::class, 'saveDraft']);
        Route::post('/purchase-orders/{purchaseOrder}/handoff', [PurchaseOrderController::class, 'handoff']);
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

        Route::get('/health', function (Request $request) {
            return response()->json([
                'status' => 'ok',
                'shop_id' => $request->user()->defaultShop()?->id,
            ]);
        });
    });
});
