<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplyChainController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Models\User;

// Authentication Routes
Route::post('/login', [UserController::class, 'authenticate']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// User Management
Route::post('/register', [UserController::class, 'store']);
Route::put('/update-user/{id}', [UserController::class, 'update']);
Route::delete('/delete-user/{id}', [UserController::class, 'deleteUser']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Inventory Management
    Route::get('/inventory', [SupplyChainController::class, 'getInventory']);
    Route::post('/inventory', [SupplyChainController::class, 'addInventory']);
    Route::put('/inventory/{id}', [SupplyChainController::class, 'updateInventory']);
    Route::delete('/inventory/{id}', [SupplyChainController::class, 'deleteInventory']);

    // Order Management
    Route::get('/orders', [SupplyChainController::class, 'getOrders']);
    Route::post('/orders', [SupplyChainController::class, 'createOrder']);
    Route::put('/orders/{id}', [SupplyChainController::class, 'updateOrder']);

    // Delivery Tracking
    Route::get('/deliveries', [SupplyChainController::class, 'getDeliveries']);
    Route::put('/deliveries/{id}', [SupplyChainController::class, 'updateDeliveryStatus']);

    // Notifications System
    Route::get('/low-stock-alert', [SupplyChainController::class, 'lowStockAlert']);
});
