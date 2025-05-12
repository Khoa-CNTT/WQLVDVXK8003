<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
    // Kiểm tra đăng nhập
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ], 401);
    }

    // Kiểm tra admin
    if (Auth::user()->role_id !== 1) {
        return response()->json([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ], 403);
    }

    return $next($request);
}
}
