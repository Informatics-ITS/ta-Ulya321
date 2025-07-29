<?php

use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Menu Routes
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{id}', [MenuController::class, 'show']);
Route::get('/vendors/{vendorId}/menus', [MenuController::class, 'getByVendor']);

// Vendor Routes
Route::get('/vendors', [VendorController::class, 'index']);
Route::get('/vendors/{id}', [VendorController::class, 'show']);
Route::get('/vendors/{id}/qris', [VendorController::class, 'getQris']);

// User Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Order Routes
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/{id}/payment-proof', [OrderController::class, 'uploadPaymentProof']);
    Route::put('/{id}/delivery-time', [OrderController::class, 'updateDeliveryTime']);
    Route::put('/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/vendor/{vendorId}/qris', [OrderController::class, 'getVendorQris']);
});