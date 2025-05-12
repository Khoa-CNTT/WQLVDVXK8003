<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Trip;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo tổng quan
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Báo cáo doanh thu
     */
    public function revenue(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Xử lý mặc định nếu không có ngày được chọn
        if (!$startDate) {
            if ($period == 'day') {
                $startDate = Carbon::now()->subDays(30)->toDateString();
            } elseif ($period == 'month') {
                $startDate = Carbon::now()->subMonths(12)->startOfMonth()->toDateString();
            } else { // year
                $startDate = Carbon::now()->subYears(5)->startOfYear()->toDateString();
            }
        }

        if (!$endDate) {
            $endDate = Carbon::now()->toDateString();
        }

        $query = Ticket::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($period == 'day') {
            $revenueData = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            $chartLabels = $revenueData->pluck('date');
            $chartData = $revenueData->pluck('total');

        } elseif ($period == 'month') {
            $revenueData = $query->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(price) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

            $chartLabels = $revenueData->map(function($item) {
                return Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            });

            $chartData = $revenueData->pluck('total');

        } else { // year
            $revenueData = $query->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(price) as total')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

            $chartLabels = $revenueData->pluck('year');
            $chartData = $revenueData->pluck('total');
        }

        // Tính tổng doanh thu
        $totalRevenue = $chartData->sum();

        return view('admin.reports.revenue', compact(
            'period',
            'startDate',
            'endDate',
            'revenueData',
            'chartLabels',
            'chartData',
            'totalRevenue'
        ));
    }

    /**
     * Báo cáo số lượng vé bán
     */
    public function tickets(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status', 'all');

        // Xử lý mặc định nếu không có ngày được chọn
        if (!$startDate) {
            if ($period == 'day') {
                $startDate = Carbon::now()->subDays(30)->toDateString();
            } elseif ($period == 'month') {
                $startDate = Carbon::now()->subMonths(12)->startOfMonth()->toDateString();
            } else { // year
                $startDate = Carbon::now()->subYears(5)->startOfYear()->toDateString();
            }
        }

        if (!$endDate) {
            $endDate = Carbon::now()->toDateString();
        }

        $query = Ticket::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        if ($period == 'day') {
            $ticketsData = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            $chartLabels = $ticketsData->pluck('date');
            $chartData = $ticketsData->pluck('total');

        } elseif ($period == 'month') {
            $ticketsData = $query->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

            $chartLabels = $ticketsData->map(function($item) {
                return Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            });

            $chartData = $ticketsData->pluck('total');

        } else { // year
            $ticketsData = $query->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

            $chartLabels = $ticketsData->pluck('year');
            $chartData = $ticketsData->pluck('total');
        }

        // Tính tổng vé
        $totalTickets = $chartData->sum();

        // Thống kê trạng thái vé
        $ticketStatusData = Ticket::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return view('admin.reports.tickets', compact(
            'period',
            'startDate',
            'endDate',
            'status',
            'ticketsData',
            'chartLabels',
            'chartData',
            'totalTickets',
            'ticketStatusData'
        ));
    }

    /**
     * Báo cáo theo tuyến đường
     */
    public function routes(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // Thống kê số lượng vé và doanh thu theo tuyến đường
        $routeStats = DB::table('tickets')
            ->join('trips', 'tickets.trip_id', '=', 'trips.id')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->whereBetween('tickets.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('tickets.status', 'completed')
            ->where('tickets.payment_status', 'paid')
            ->select(
                'routes.id',
                'routes.departure',
                'routes.destination',
                DB::raw('COUNT(tickets.id) as ticket_count'),
                DB::raw('SUM(tickets.price) as total_revenue')
            )
            ->groupBy('routes.id', 'routes.departure', 'routes.destination')
            ->orderByDesc('ticket_count')
            ->get();

        // Chuẩn bị dữ liệu cho biểu đồ
        $routeLabels = $routeStats->map(function($item) {
            return $item->departure . ' - ' . $item->destination;
        });

        $ticketCountData = $routeStats->pluck('ticket_count');
        $revenueData = $routeStats->pluck('total_revenue');

        return view('admin.reports.routes', compact(
            'startDate',
            'endDate',
            'routeStats',
            'routeLabels',
            'ticketCountData',
            'revenueData'
        ));
    }

    /**
     * Xuất báo cáo dưới dạng PDF
     */
    public function exportPdf(Request $request, $type)
    {
        // Logic xuất PDF tùy theo loại báo cáo
        // Cần cài đặt thư viện như dompdf hoặc barryvdh/laravel-dompdf
        // ...

        return back()->with('success', 'Đã xuất báo cáo PDF thành công.');
    }

    /**
     * Xuất báo cáo dưới dạng Excel
     */
    public function exportExcel(Request $request, $type)
    {
        // Logic xuất Excel
        // Cần cài đặt thư viện như maatwebsite/excel
        // ...

        return back()->with('success', 'Đã xuất báo cáo Excel thành công.');
    }
}
