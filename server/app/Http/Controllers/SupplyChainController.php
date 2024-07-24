<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class SupplyChainController extends Controller
{
   
    // Inventory Management: CRUD Operations
    public function getInventory()
    {
          $inventory = Inventory::all();
        return response()->json($inventory);
    }

    public function addInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_level' => 'required|integer|min:0', // Ensure stock_level is provided and is a non-negative integer
            'price' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        // Get the stock_level from the request
        $inValue = $request->input('stock_level');
    
        // Create the inventory with stock_level set to 0
        $inventory = Inventory::create([
            'product_name' => $request->input('product_name'),
            'description' => $request->input('description'),
            'stock_level' => 0, // Initially set stock_level to 0
            'price' => $request->input('price'),
        ]);
    
        // Create a transaction for adding new inventory
        $suppliers = User::where('user_type', 'supplier')->pluck('id');
        foreach ($suppliers as $supplierId) {
            Transaction::create([
                'user_id' => $supplierId,
                'inventory_id' => $inventory->id,
                'transaction_type' => 'inventory_management',
                'status' => 'pending',
                'in_value' => $inValue, // Use the stock_level from the request as in_value
                'order_date' => now(),
            ]);
        }
    
        return response()->json(['message' => 'Product added successfully', 'inventory' => $inventory], 201);
    }


public function getInventoryItem($id)
{
    // Fetch the inventory item by its ID
    $inventory = Inventory::findOrFail($id);
    
    return response()->json($inventory);
}


    

    public function updateInventoryItem(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
    
        // Update inventory item details
        $inventory->product_name = $request->product_name;
        $inventory->description = $request->description;
        $inventory->price = $request->price; // Update price
    
        $transaction = new Transaction();
        $transaction->user_id = $request->user_id;
        $transaction->inventory_id = $inventory->id;
        $transaction->transaction_type = 'inventory_management';
        $transaction->status = 'delivered';
        $transaction->order_date = now();
    
        if ($request->filled('in_value')) {
            $inventory->stock_level += $request->in_value;
            $transaction->in_value = $request->in_value;
        }
    
        if ($request->filled('out_value')) {
            $inventory->stock_level -= $request->out_value;
            $transaction->out_value = $request->out_value;
        }
    
        $inventory->save();
        $transaction->save();
    
        return response()->json(['message' => 'Inventory item updated successfully', 'inventory' => $inventory], 200);
    }
    
    

    public function deleteInventory($id)
    {
       

        $inventory = Inventory::findOrFail($id);
        $inventory->delete();

        // Delete corresponding transactions
        Transaction::where('inventory_id', $id)->delete();

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
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventory,id',
            'out_value' => 'required|integer',
            'order_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Transaction::create([
            'user_id' => $request->user_id,
            'inventory_id' => $request->inventory_id,
            'transaction_type' => 'delivery',
            'status' => 'pending',
            'out_value' => $request->out_value,
            'transaction_date' => now(),
            'order_date' => $request->order_date,
        ]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function updateOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,en-route,delivered',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $order = Transaction::findOrFail($id);
        
        // Check if the status is updated to 'delivered'
        if ($order->status !== 'delivered' && $request->status === 'delivered') {
            // Update the inventory's stock_level based on the in_value of the transaction
            $inventory = Inventory::findOrFail($order->inventory_id);
            $inventory->stock_level += $order->in_value;
            $inventory->save();
        }
    
        // Update the transaction with the new status and any other changes
        $order->update($request->all());
    
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }
    
    public function getSuppliersOrders(Request $request)
    {
        $userId = $request->input('user_id'); // Get user_id from the request
    
      
        // Fetch orders with product names
        $deliveries = Transaction::join('inventory', 'transactions.inventory_id', '=', 'inventory.id')
                                 ->where('transactions.user_id', $userId)
                                 ->where('transactions.transaction_type', 'inventory_management')
                                 ->select('transactions.*', 'inventory.product_name')
                                 ->get();
    
        return response()->json($deliveries);
    }
    
    

    public function getDriverDeliveries(Request $request)
    {
        // Validate the user_id
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid user ID'], 400);
        }
    
        // Fetch deliveries based on the provided user_id
        $deliveries = Transaction::join('inventory', 'transactions.inventory_id', '=', 'inventory.id')
                                 ->where('transactions.user_id', $request->user_id)
                                 ->where('transactions.transaction_type', 'delivery')
                                 ->where('transactions.status', '!=', 'delivered')
                                 ->select('transactions.*', 'inventory.product_name')
                                 ->get();
    
        return response()->json($deliveries);
    }
    

    public function updateOrderDriver(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,en-route,delivered',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $order = Transaction::findOrFail($id);
        
        // Check if the status is updated to 'delivered'
        if ($order->status !== 'delivered' && $request->status === 'delivered') {
            // Update the inventory's stock_level based on the in_value of the transaction
            $inventory = Inventory::findOrFail($order->inventory_id);
            $inventory->stock_level -= $order->out_value;
            $inventory->save();
        }
    
        // Update the transaction with the new status and any other changes
        $order->update($request->all());
    
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }
    
    

    // Get All Deliveries (Differentiated by Type)
    public function getAllDeliveries()
    {
        if (!in_array(Auth::user()->user_type, ['warehouse_manager', 'supplier', 'delivery_driver', 'system_administrator'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deliveries = Transaction::all();
        $deliveryData = [
            'inventory_management' => $deliveries->where('transaction_type', 'inventory_management'),
            'delivery' => $deliveries->where('transaction_type', 'delivery'),
        ];

        return response()->json($deliveryData);
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        // Validation for status
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,en_route,delivered',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        // Find the delivery transaction
        $delivery = Transaction::findOrFail($id);
    
        // Check user type and permissions
        if ($user->user_type === 'delivery_driver') {
            // Delivery drivers can only update their own transactions
            if ($delivery->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif ($user->user_type === 'supplier') {
            // Suppliers can only update inventory_management transactions related to them
            if ($delivery->user_id !== $user->id || $delivery->transaction_type !== 'inventory_management') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif (!in_array($user->user_type, ['warehouse_manager', 'system_administrator'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Update the delivery status
        $delivery->status = $request->status;
        $delivery->transaction_date = now();
    
        if ($request->status === 'delivered') {
            // Handle inventory update
            $inventory = Inventory::find($delivery->inventory_id);
    
            if ($inventory) {
                if ($delivery->transaction_type === 'delivery') {
                    // Adjust stock level based on out_value
                    $inventory->stock_level -= $delivery->out_value;
                } elseif ($delivery->transaction_type === 'inventory_management') {
                    // For inventory management, adjust stock level based on in_value
                    $inventory->stock_level += $delivery->in_value;
                }
    
                $inventory->save();
            }
        }
    
        $delivery->save();
        
        return response()->json(['message' => 'Delivery status updated successfully', 'delivery' => $delivery], 200);
    }
    
    
   
    public function getUsersForDropdown()
{

    $suppliers = User::where('user_type', 'supplier')->get(['id', 'name']);
    $deliveryDrivers = User::where('user_type', 'delivery_driver')->get(['id', 'name']);

    return response()->json([
        'suppliers' => $suppliers,
        'delivery_drivers' => $deliveryDrivers,
    ]);
}

public function getDeliveries()
{
    $deliveries = DB::table('transactions')
        ->join('users', 'transactions.user_id', '=', 'users.id')
        ->join('inventory', 'transactions.inventory_id', '=', 'inventory.id')
        ->select('transactions.id', 'inventory.product_name', 'transactions.status', 'transactions.out_value', 'users.name as deliverer_name')
        ->where('transactions.transaction_type', 'delivery') // Assuming 'type' distinguishes between transaction types
        ->get();

    return response()->json($deliveries);
}

public function updateDeliveryStatusManager($id, Request $request)
{
    $status = $request->input('status');

    // Validate status
    if (!in_array($status, ['pending', 'en-route', 'delivered'])) {
        return response()->json(['error' => 'Invalid status'], 400);
    }

    $updated = \DB::table('transactions')
        ->where('id', $id)
        ->update(['status' => $status]);

    if ($updated) {
        return response()->json(['message' => 'Delivery status updated successfully']);
    } else {
        return response()->json(['error' => 'Failed to update status'], 500);
    }
}




public function lowStockAlert()
{
    $lowStockItems = Inventory::where('stock_level', '<', 10)->get(); // Threshold set to 10

    if ($lowStockItems->isEmpty()) {
        return response()->json([
            'message' => 'All stock levels are sufficient',
            'items' => [] // Return an empty array for consistency
        ], 200);
    }

    return response()->json([
        'message' => 'Low stock alert',
        'items' => $lowStockItems->toArray() // Convert to array
    ], 200);
}



public function orderNotifications()
{
    
    $yesterday = Carbon::now()->subHours(24);

    $orders = DB::table('transactions')
        ->join('users', 'transactions.user_id', '=', 'users.id')
        ->join('inventory', 'transactions.inventory_id', '=', 'inventory.id')
        ->select(
            'transactions.id',
            'inventory.product_name',
            'transactions.status',
            'transactions.out_value',
            'users.name as deliverer_name',
            'transactions.created_at'
        )
        ->whereIn('transactions.status', ['pending', 'en-route'])
        ->where('transactions.transaction_date', '>=', $yesterday)
        ->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No recent orders pending or en-route'], 200);
    }

    return response()->json([
        'message' => 'Order notifications',
        'orders' => $orders
    ], 200);
}


public function updateUser(Request $request, $id)
{
    $user = User::find($id);
    
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'user_type' => 'required|string',
    ]);

    $user->update($validated);
    
    return response()->json(['message' => 'User updated successfully']);
}

public function getUsers()
{
    $users = User::where('user_type', '!=', 'system_administrator')->get();
    return response()->json($users);
}

public function show($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
}

public function destroy($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->delete();
    return response()->json(['message' => 'User deleted successfully']);
}


}
