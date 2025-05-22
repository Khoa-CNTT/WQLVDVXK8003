<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Lấy đường dẫn redirect người dùng khi chưa xác thực.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            abort(401, 'Unauthorized');
        }
        // Nếu là web, có thể redirect về trang login (nếu muốn)
        // return route('login');
        return null;
    }
}
