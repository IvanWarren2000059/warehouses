<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->status === 'Deactivated') {
                Auth::logout();
                return response()->json(['message' => 'Account is deactivated. Please contact support.'], 403);
            }
    
            $token = $user->createToken('YourAppName')->plainTextToken; 
            return response()->json([
                'access_token' => $token, 
                'id' => $user->id,
                'name' => $user->name,
                'user_Type' => $user->user_Type,
                'message' => 'Authenticated successfully'
            ]);
        }
    
        return response()->json(['message' => 'The provided credentials do not match our records.'], 401);
    }
    
    

    
}
