<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index($id = null)
    {
        try {
            if ($id) {
                $user = User::findOrFail($id);
                return response()->json($user);
            }
            $users = User::all(['id', 'name', 'email', 'user_type']);
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve users'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|min:1',
                'confirm_password' => 'required|min:1|same:password',
                'user_type' => 'required|string|in:warehouse_manager,supplier,delivery_driver,system_administrator',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'user_type' => $request->user_type,
            ]);

            Log::info('User created successfully: ' . $user->id);
            return response()->json($user, 201);

        } catch (ValidationException $e) {
            Log::error('Validation errors occurred: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create user'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|nullable|min:1',
                'confirm_password' => 'sometimes|nullable|min:1|same:password',
                'user_type' => 'required|string|in:warehouse_manager,supplier,delivery_driver,system_administrator',
            ]);

            $user = User::findOrFail($id);

            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->user_type = $request->user_type;
            $user->save();

            Log::info('User updated successfully: ' . $user->id);
            return response()->json(['message' => 'User profile updated successfully'], 200);
        } catch (ValidationException $e) {
            Log::error('Validation errors occurred: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Log::info('User deleted successfully: ' . $id);
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete user'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Example of token-based authentication

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
        ]);
    }

    // Logout function
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
