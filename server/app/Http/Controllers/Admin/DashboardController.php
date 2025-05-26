<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Line;
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

    public function apiStats()
    {
        // Đếm số chuyến xe hôm nay
        $todayTripCount = Trip::whereDate('departure_time', Carbon::today())->count();
        $ticketCount = Ticket::count();
        $driversCount = Driver::count();
        $vehiclesCount = Vehicle::count();
        $linesCount = Line::count();

        // Dữ liệu động cho kinh nghiệm tài xế (tự động lấy tất cả các nhóm năm kinh nghiệm thực tế)
        $experienceGroups = Driver::select('experience_years')->distinct()->pluck('experience_years')->sort()->values();
        $driverExpCounts = [];
        foreach ($experienceGroups as $exp) {
            $driverExpCounts[] = Driver::where('experience_years', $exp)->count();
        }
        $driverExperienceData = [
            'labels' => $experienceGroups->map(function($y) { return $y . ' năm'; }),
            'datasets' => [[
                'label' => 'Số tài xế',
                'data' => $driverExpCounts,
                'backgroundColor' => '#f97316',
                'borderColor' => '#ea580c',
                'borderWidth' => 1
            ]]
        ];

        // Dữ liệu động cho loại phương tiện
        $vehicleTypes = Vehicle::select('type')->distinct()->pluck('type');
        $vehicleCounts = [];
        foreach ($vehicleTypes as $type) {
            $vehicleCounts[] = Vehicle::where('type', $type)->count();
        }
        $vehicleData = [
            'labels' => $vehicleTypes,
            'datasets' => [[
                'label' => 'Phương tiện',
                'data' => $vehicleCounts,
                'backgroundColor' => [
                    '#3b82f6', '#ef4444', '#facc15', '#22c55e', '#f97316', '#10b981'
                ],
                'borderWidth' => 1
            ]]
        ];

        $lines = Line::all(); // Lấy danh sách tất cả tuyến đường

        return response()->json([
            'todayTripCount' => $todayTripCount,
            'ticketCount' => $ticketCount,
            'driversCount' => $driversCount,
            'vehiclesCount' => $vehiclesCount,
            'linesCount' => $linesCount,
            'lines' => $lines, // Trả về danh sách tuyến đường
            'driverExperienceData' => $driverExperienceData,
            'vehicleData' => $vehicleData
        ]);
    }
}
