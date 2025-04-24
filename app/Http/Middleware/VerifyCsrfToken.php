<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Các URI được loại trừ khỏi xác minh CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
