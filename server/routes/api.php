<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\LineController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\SeatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ChatbotController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Các routes API cho ứng dụng đặt vé xe khách Phương Thanh Express
|
*/

// Route group cho các API không cần xác thực
Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Tìm kiếm và hiển thị thông tin tuyến đường
    Route::get('lines/search', [LineController::class, 'search']);
    Route::get('lines', [LineController::class, 'index']);
    Route::get('lines/{id}', [LineController::class, 'show']);

    // Tìm kiếm và hiển thị chuyến xe
    Route::get('trips/search', [TripController::class, 'search']);
    Route::get('trips/{id}', [TripController::class, 'show']);

    // Chatbot
    Route::post('chatbot/query', [ChatbotController::class, 'handleQuery']);
});

// Route group cho các API yêu cầu xác thực
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('user', [UserController::class, 'profile']);
    Route::put('user', [UserController::class, 'update']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('trips', TripController::class);

    // Đặt vé
    Route::get('trips/{trip_id}/seats', [SeatController::class, 'getTripSeats']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings', [BookingController::class, 'index']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::delete('bookings/{id}', [BookingController::class, 'cancel']);

    // Vé của user
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);

    // Thanh toán
    Route::post('payments/vnpay/create', [PaymentController::class, 'createVnpayPayment']);
    Route::post('payments/momo/create', [PaymentController::class, 'createMomoPayment']);
    Route::get('payments/callback', [PaymentController::class, 'handlePaymentCallback']);
});

// Route group cho các API chỉ dành cho admin
Route::prefix('v1/admin')->middleware(['auth:sanctum','admin'])->group(function () {
    // Quản lý tuyến đường
    Route::apiResource('lines', LineController::class);

    // Quản lý phương tiện
    Route::apiResource('vehicles', VehicleController::class);

    // Quản lý tài xế
    Route::apiResource('drivers', DriverController::class);

    // Quản lý chuyến xe
    Route::apiResource('trips', TripController::class);

    // Quản lý ghế
    Route::apiResource('seats', SeatController::class);

    // Quản lý người dùng
    Route::apiResource('users', UserController::class);

    // Thống kê báo cáo
    Route::get('reports/revenue', [BookingController::class, 'revenueReport']);
    Route::get('reports/tickets', [TicketController::class, 'ticketReport']);
    Route::get('reports/trips', [TripController::class, 'tripReport']);

    // Quản lý vé và đặt vé
    Route::get('bookings', [BookingController::class, 'adminIndex']);
    Route::put('bookings/{id}/status', [BookingController::class, 'updateStatus']);
    Route::get('tickets/all', [TicketController::class, 'adminIndex']);
    Route::put('tickets/{id}/status', [TicketController::class, 'updateStatus']);

    // Quản lý chatbot
    Route::get('chatbot/logs', [ChatbotController::class, 'logs']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'apiStats']);
});

// Chatbot routes
Route::prefix('v1/chatbot')->group(function () {
    Route::post('query', [ChatbotController::class, 'handleQuery']);
    Route::get('history', [ChatbotController::class, 'getHistory'])->middleware('auth:sanctum');
    Route::delete('history', [ChatbotController::class, 'clearHistory'])->middleware('auth:sanctum');
});

Route::post('/chat', [App\Http\Controllers\ChatbotController::class, 'chat']);
