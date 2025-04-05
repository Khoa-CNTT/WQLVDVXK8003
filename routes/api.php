<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🔹 Xác thực người dùng
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 🔹 Middleware Sanctum cho các route cần xác thực
Route::middleware(['auth:sanctum', EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::apiResource('buses', BusController::class);
    Route::apiResource('routes', RouteController::class);
    Route::apiResource('tickets', TicketController::class);
});

// 🔹 Thanh toán VNPAY
Route::post('/payment/vnpay', [PaymentController::class, 'payVnpay']);
Route::get('/payment-success', [PaymentController::class, 'paymentSuccess']);
Route::get('/payment/vnpay-return', [PaymentController::class, 'vnpayReturn'])->name('vnpay.return');
