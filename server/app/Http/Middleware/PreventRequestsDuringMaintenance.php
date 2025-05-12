<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * Các URI vẫn có thể truy cập được trong chế độ bảo trì.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
