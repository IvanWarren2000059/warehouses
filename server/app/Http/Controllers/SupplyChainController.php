<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplyChainController extends Controller
{
    // Middleware to check user type
    public function __construct()
    {
        $this->middleware('auth');
    }

    // User Management: Register, Update, and Delete Users
    public function registerUser(Request $request)
    {
        if (Auth::user()->user_type !== 'system_administrator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|string|in:warehouse_manager,supplier,delivery_driver,system_administrator',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->user_type !== 'system_administrator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function deleteUser($id)
    {
        if (Auth::user()->user_type !== 'system_administrator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Inventory Management: CRUD Operations
    public function getInventory()
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::all();
        return response()->json($inventory);
    }

    public function addInventory(Request $request)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_level' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $inventory = Inventory::create($request->all());
        return response()->json(['message' => 'Product added successfully', 'inventory' => $inventory], 201);
    }

    public function updateInventory(Request $request, $id)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());
        return response()->json(['message' => 'Product updated successfully', 'inventory' => $inventory], 200);
    }

    public function deleteInventory($id)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    // Order Management
    public function getOrders()
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $orders = Transaction::all();
        return response()->json($orders);
    }

    public function createOrder(Request $request)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'inventory_id' => 'required|exists:inventories,id',
            'status' => 'required|string|in:pending,completed,cancelled',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Transaction::create($request->all());
        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function updateOrder(Request $request, $id)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order = Transaction::findOrFail($id);
        $order->update($request->all());
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    // Delivery Tracking
    public function getDeliveries()
    {
        if (Auth::user()->user_type !== 'delivery_driver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deliveries = Transaction::where('status', '!=', 'completed')->get();
        return response()->json($deliveries);
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        if (Auth::user()->user_type !== 'delivery_driver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,en_route,delivered',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $delivery = Transaction::findOrFail($id);
        $delivery->status = $request->status;
        $delivery->save();
        return response()->json(['message' => 'Delivery status updated successfully', 'delivery' => $delivery], 200);
    }

    // Notifications System (Mock-up, not a complete implementation)
    public function lowStockAlert()
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lowStockItems = Inventory::where('stock_level', '<', 10)->get(); // Assuming threshold is 10
        if ($lowStockItems->isEmpty()) {
            return response()->json(['message' => 'All stock levels are sufficient'], 200);
        }

        // Here you could send notifications to the warehouse manager
        return response()->json(['message' => 'Low stock alert', 'items' => $lowStockItems], 200);
    }
}
