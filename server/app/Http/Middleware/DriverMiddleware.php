<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DriverMiddleware
{
    /**
     * Xử lý request đến.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        // Giả sử role_id = 2 là driver, dựa vào cấu trúc của phương thức isAdmin()
        if (auth()->user()->role_id !== 2) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden. Driver access required.'], 403)
                : redirect()->route('home')->with('error', 'Quyền truy cập bị từ chối. Chỉ dành cho tài xế.');
        }

        return $next($request);
    }
}
