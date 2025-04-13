<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\RouteController as AdminRouteController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ và trang tĩnh
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/security', [HomeController::class, 'security'])->name('security');
Route::get('/utilities', [HomeController::class, 'utilities'])->name('utilities');

// Routes xác thực
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Đặt vé
    Route::get('/booking', [BookingController::class, 'searchForm'])->name('booking.search');
    Route::get('/booking-results', [BookingController::class, 'search'])->name('booking.results');
    Route::get('/trip/{tripId}/seats', [BookingController::class, 'seats'])->name('booking.seats');
    Route::post('/passenger-details', [BookingController::class, 'passengerDetails'])->name('booking.passenger');

    // Tickets
    Route::post('/tickets', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.my');
    Route::post('/tickets/{id}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');

    // Payments
    Route::get('/payments/process', [PaymentController::class, 'process'])->name('payments.process');
});

// Payment callbacks
Route::get('/payments/callback/vnpay', [PaymentController::class, 'callbackVnpay'])->name('payments.callback.vnpay');

// Chatbot API
Route::post('/chatbot/query', [ChatbotController::class, 'processQuery'])->name('chatbot.query');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Routes
    Route::resource('routes', AdminRouteController::class);

    // Drivers
    Route::resource('drivers', DriverController::class);

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Trips
    Route::resource('trips', TripController::class);

    // Tickets management
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::post('/tickets/{id}/update-status', [AdminTicketController::class, 'updateStatus'])->name('admin.tickets.update-status');

    // Reports
    Route::get('/reports/sales', [DashboardController::class, 'salesReport'])->name('admin.reports.sales');
    Route::get('/reports/routes', [DashboardController::class, 'routesReport'])->name('admin.reports.routes');

    Route::get('/', [HomeController::class, 'index']);
});

