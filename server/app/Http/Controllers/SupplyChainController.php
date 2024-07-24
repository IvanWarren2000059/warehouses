<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
    

    public function updateInventory(Request $request, $id)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());

        // Update transaction for stock level change
        Transaction::where('inventory_id', $id)->update([
            'in_value' => $inventory->stock_level,
            'transaction_date' => now(),
        ]);

        return response()->json(['message' => 'Product updated successfully', 'inventory' => $inventory], 200);
    }

    public function deleteInventory($id)
    {
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
        if (Auth::user()->user_type !== 'warehouse_manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventory,id',
            'out_value' => 'required|integer',
            'order_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Transaction::create([
            'user_id' => Auth::id(),
            'inventory_id' => $request->inventory_id,
            'transaction_type' => 'delivery',
            'out_value' => $request->out_value,
            'order_date' => $request->order_date,
        ]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function updateOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,en_route,delivered',
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
    
    

    // Delivery Tracking for Delivery Driver
    public function getDriverDeliveries()
    {
        if (Auth::user()->user_type !== 'delivery_driver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deliveries = Transaction::where('user_id', Auth::id())
                                 ->where('transaction_type', 'delivery')
                                 ->where('status', '!=', 'delivered')
                                 ->get();
        return response()->json($deliveries);
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


    public function getUsersForDropdown()
{

    $suppliers = User::where('user_type', 'supplier')->get(['id', 'name']);
    $deliveryDrivers = User::where('user_type', 'delivery_driver')->get(['id', 'name']);

    return response()->json([
        'suppliers' => $suppliers,
        'delivery_drivers' => $deliveryDrivers,
    ]);
}

}
