<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Hiển thị danh sách vé của người dùng hiện tại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with(['trip.route', 'trip.vehicle', 'seat', 'booking'])
            ->whereHas('booking', function($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Hiển thị danh sách tất cả vé (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function adminIndex(Request $request)
    {
        $query = Ticket::with(['trip.route', 'trip.vehicle', 'seat', 'booking.user']);

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo chuyến xe
        if ($request->has('trip_id')) {
            $query->where('trip_id', $request->trip_id);
        }

        // Lọc theo ngày
        if ($request->has('date')) {
            $query->whereHas('trip', function($query) use ($request) {
                $query->whereDate('departure_time', $request->date);
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Hiển thị chi tiết vé
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = Ticket::with(['trip.route', 'trip.vehicle', 'trip.driver', 'seat', 'booking'])
            ->whereHas('booking', function($query) {
                $query->where('user_id', request()->user()->id);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Cập nhật trạng thái vé (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        // Cập nhật trạng thái booking nếu tất cả vé của booking này đều có cùng trạng thái
        $booking = $ticket->booking;
        $allTicketsHaveSameStatus = Ticket::where('booking_id', $booking->id)
                                         ->where('status', '!=', $request->status)
                                         ->doesntExist();

        if ($allTicketsHaveSameStatus) {
            $booking->status = $request->status;
            $booking->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái vé thành công',
            'data' => $ticket
        ]);
    }

    /**
     * Báo cáo vé (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ticketReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

// Kiểm tra nếu start_date không được cung cấp, sử dụng ngày hôm nay
$startDate = $request->has('start_date')
    ? Carbon::parse($request->start_date)->startOfDay()
    : Carbon::today()->startOfDay();

// Kiểm tra nếu end_date không được cung cấp, sử dụng ngày hôm nay
$endDate = $request->has('end_date')
    ? Carbon::parse($request->end_date)->endOfDay()
    : Carbon::today()->endOfDay();

        // Thống kê số vé theo trạng thái
        $statusStats = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        // Thống kê số vé theo ngày
        $dailyStats = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as ticket_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Thống kê số vé theo tuyến đường
        $routeStats = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate])
            ->join('trips', 'tickets.trip_id', '=', 'trips.id')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->select(
                'routes.id',
                'routes.departure',
                'routes.destination',
                DB::raw('COUNT(tickets.id) as ticket_count')
            )
            ->groupBy('routes.id', 'routes.departure', 'routes.destination')
            ->orderBy('ticket_count', 'desc')
            ->get();

        // Tổng hợp
        $totalTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalRevenue = Ticket::whereBetween('tickets.created_at', [$startDate, $endDate])
            ->where('tickets.status', '!=', 'cancelled')
            ->join('trips', 'tickets.trip_id', '=', 'trips.id')
            ->sum('trips.price');

        return response()->json([
            'success' => true,
            'data' => [
                'status_stats' => $statusStats,
                'daily_stats' => $dailyStats,
                'route_stats' => $routeStats,
                'summary' => [
                    'total_tickets' => $totalTickets,
                    'total_revenue' => $totalRevenue
                ],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
