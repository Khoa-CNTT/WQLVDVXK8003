<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Tổng doanh thu
        $totalRevenue = Ticket::where('status', 'completed')->sum('price');

        // Doanh thu tháng hiện tại
        $currentMonthRevenue = Ticket::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');

        // Tổng số vé đã bán
        $totalTickets = Ticket::where('status', 'completed')->count();

        // Số vé đã bán trong tháng
        $currentMonthTickets = Ticket::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Thống kê vé theo trạng thái
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Chuyến xe trong ngày
        $todayTrips = Trip::whereDate('departure_time', Carbon::today())->count();

        // Tổng số khách hàng đã đăng ký
        $totalCustomers = User::where('role', 'customer')->count();

        // Tổng số tài xế
        $totalDrivers = Driver::count();

        // Biểu đồ doanh thu 7 ngày gần nhất
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Ticket::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('price');

            $revenueChart[] = [
                'date' => $date->format('d/m'),
                'revenue' => $revenue
            ];
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'currentMonthRevenue',
            'totalTickets',
            'currentMonthTickets',
            'ticketsByStatus',
            'todayTrips',
            'totalCustomers',
            'totalDrivers',
            'revenueChart'
        ));
    }
}
