<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplyChainController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\UserController;

// Authentication Routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// User Management
Route::post('/register', [UserController::class, 'store']);
Route::put('/update-user/{id}', [UserController::class, 'update']);
Route::delete('/delete-user/{id}', [UserController::class, 'deleteUser']);

// Inventory Management (No Authentication)
Route::get('/inventory', [SupplyChainController::class, 'getInventory']);
Route::post('/inventory', [SupplyChainController::class, 'addInventory']);
Route::put('/inventory/{id}', [SupplyChainController::class, 'updateInventory']);
Route::delete('/inventory/{id}', [SupplyChainController::class, 'deleteInventory']);

// Order Management (No Authentication)
Route::get('/orders', [SupplyChainController::class, 'getOrders']);
Route::get('/getSupplierOrders', [SupplyChainController::class, 'getSuppliersOrders']);

Route::post('/orders', [SupplyChainController::class, 'createOrder']);
Route::put('/orders/{id}', [SupplyChainController::class, 'updateOrder']);

// Delivery Tracking (No Authentication)
Route::get('/deliveries', [SupplyChainController::class, 'getAllDeliveries']);
Route::put('/deliveries/{id}', [SupplyChainController::class, 'updateDeliveryStatus']);

// Notifications System (No Authentication)
Route::get('/low-stock-alert', [SupplyChainController::class, 'lowStockAlert']);

// Users for Dropdown (No Authentication)
Route::get('/users-for-dropdown', [SupplyChainController::class, 'getUsersForDropdown']);
