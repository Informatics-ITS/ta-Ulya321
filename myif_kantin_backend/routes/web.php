<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
  return redirect()->route('auth.login');
})->name('home');

Route::controller(AuthController::class)->group(function () {
  Route::get('/login', 'login')->name('auth.login');
  Route::post('/login', 'authenticate')->name('auth.authenticate');
});

Route::middleware('check.session')->group(function () {
  Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

  Route::prefix('users')->group(function () {
      Route::get('/', [UserController::class, 'index'])->name('users.index');
      Route::post('/save', [UserController::class, 'saveUser'])->name('users.save');
      Route::delete('/{id}', [UserController::class, 'deleteUser'])->name('users.delete');
  });

  Route::prefix('vendors')->group(function () {
      Route::get('/', [VendorController::class, 'index'])->name('vendors.index');
      Route::post('/save', [VendorController::class, 'saveVendor'])->name('vendors.save');
      Route::delete('/{id}', [VendorController::class, 'deleteVendor'])->name('vendors.delete');
  });

  Route::prefix('menus')->group(function () {
      Route::get('/', [MenuController::class, 'index'])->name('menus.index');
      Route::post('/save', [MenuController::class, 'saveMenu'])->name('menus.save');
      Route::delete('/{id}', [MenuController::class, 'deleteMenu'])->name('menus.delete');
  });

  Route::prefix('orders')->group(function () {
      Route::get('/', [OrderController::class, 'index'])->name('orders.index');
      Route::post('/save', [OrderController::class, 'saveOrder'])->name('orders.save');
      Route::delete('/{id}', [OrderController::class, 'deleteOrder'])->name('orders.delete');
      Route::post('/status', [OrderController::class, 'updateStatus'])->name('orders.status');
      Route::post('/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status');
      Route::get('/menus-by-vendor', [OrderController::class, 'getMenusByVendor'])->name('orders.menus-by-vendor');
      Route::get('/details/{id}', [OrderController::class, 'getOrderDetails'])->name('orders.details');
      Route::get('/poll', [OrderController::class, 'pollNewOrders'])->name('orders.poll');
      Route::get('/changes', [OrderController::class, 'pollOrderChanges'])->name('orders.changes');
      Route::get('/vendor-qris/{vendorId}', [OrderController::class, 'getVendorQris'])->name('orders.vendor-qris');
      Route::post('/export', [OrderController::class, 'exportReport'])->name('orders.export');
  });
});