<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->expectsJson()) {
            // API request, check Sanctum authentication
            if (!Auth::guard('sanctum')->check()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
        } else {
            // Web request, check session authentication
            if (!Auth::check()) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
