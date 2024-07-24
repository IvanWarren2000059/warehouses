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

// Inventory Management  
Route::get('/inventory', [SupplyChainController::class, 'getInventory']);
Route::post('/inventory', [SupplyChainController::class, 'addInventory']);
Route::put('/inventory/{id}', [SupplyChainController::class, 'updateInventoryItem']);
Route::delete('/inventory/{id}', [SupplyChainController::class, 'deleteInventory']);
Route::get('/inventory/{id}', [SupplyChainController::class, 'getInventoryItem']);

// Order Management  
Route::get('/orders', [SupplyChainController::class, 'getOrders']);
Route::get('/getSupplierOrders', [SupplyChainController::class, 'getSuppliersOrders']);

Route::post('/orders', [SupplyChainController::class, 'createOrder']);
Route::put('/orders/{id}', [SupplyChainController::class, 'updateOrder']);
Route::put('/ordersDriver/{id}', [SupplyChainController::class, 'updateOrderDriver']);

// Delivery Tracking  
Route::get('/deliveries', [SupplyChainController::class, 'getAllDeliveries']);
Route::put('/deliveries/{id}', [SupplyChainController::class, 'updateDeliveryStatus']);
Route::get('/getDriverDeliveries', [SupplyChainController::class, 'getDriverDeliveries']);

// Notifications System  
Route::get('/low-stock-alert', [SupplyChainController::class, 'lowStockAlert']);
Route::get('/order-notifications', [SupplyChainController::class, 'orderNotifications']);
// Users for Dropdown  
Route::get('/users-for-dropdown', [SupplyChainController::class, 'getUsersForDropdown']);


Route::get('/getDeliveriesManager', [SupplyChainController::class, 'getDeliveries']);
Route::put('/updateDeliveryStatusManager/{id}', [SupplyChainController::class, 'updateDeliveryStatusManager']);


Route::get('/users', [SupplyChainController::class, 'getUsers']);
Route::put('/users/{id}', [SupplyChainController::class, 'updateUser']);
Route::get('/users/{id}', [SupplyChainController::class, 'show']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
