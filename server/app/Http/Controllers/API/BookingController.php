<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Ticket;
use App\Models\Seat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Hiển thị danh sách đặt vé của user hiện tại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $bookings = Booking::with(['trip.line', 'tickets.seat'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Hiển thị danh sách đặt vé (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function adminIndex(Request $request)
    {
        $query = Booking::with(['user', 'trip.line', 'tickets.seat']);

        // Lọc theo trạng thái nếu có
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày nếu có
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Lọc theo người dùng nếu có
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Tạo đặt vé mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'exists:seats,id',
            'passenger_name' => 'required|string|max:255',
            'passenger_phone' => 'required|string|max:20',
            'passenger_email' => 'required|email',
            'payment_method' => 'required|in:cash,vnpay,momo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra trip có tồn tại
        $trip = Trip::findOrFail($request->trip_id);

        // Kiểm tra ghế có sẵn không
        $unavailableSeats = Ticket::whereIn('seat_id', $request->seat_ids)
            ->where('trip_id', $request->trip_id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($unavailableSeats) {
            return response()->json([
                'success' => false,
                'message' => 'Một số ghế đã được đặt, vui lòng chọn ghế khác'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Tạo booking
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'trip_id' => $request->trip_id,
                'passenger_name' => $request->passenger_name,
                'passenger_phone' => $request->passenger_phone,
                'passenger_email' => $request->passenger_email,
                'seat_count' => count($request->seat_ids),
                'total_price' => $trip->price * count($request->seat_ids),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'booking_code' => 'PT' . time() . rand(1000, 9999),
            ]);

            // Tạo ticket cho mỗi ghế
            foreach ($request->seat_ids as $seat_id) {
                Ticket::create([
                    'booking_id' => $booking->id,
                    'trip_id' => $request->trip_id,
                    'seat_id' => $seat_id,
                    'status' => 'pending',
                    'ticket_code' => 'T' . time() . rand(100, 999) . $seat_id,
                ]);
            }

            DB::commit();

            // Load relationships
            $booking->load(['trip.line', 'tickets.seat']);

            // Nếu chọn VNPay thì trả về payment_url
            if ($request->payment_method === 'vnpay') {
                $paymentRes = app(\App\Http\Controllers\API\PaymentController::class)->createVnpayPayment(new Request(['booking_id' => $booking->id]));
                $paymentData = $paymentRes->getData()->data ?? null;
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_url' => $paymentData->payment_url ?? null,
                        'booking' => $booking
                    ]
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt vé thành công',
                'data' => [
                    'booking' => $booking
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi đặt vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết đặt vé
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $booking = Booking::with(['trip.line', 'tickets.seat'])
            ->where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Hủy đặt vé
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $booking = Booking::where('id', $id)
        ->where('user_id', request()->user()->id)
            ->firstOrFail();

        // Kiểm tra điều kiện hủy vé
        $trip = Trip::findOrFail($booking->trip_id);
        $departureTime = Carbon::parse($trip->departure_time);
        $now = Carbon::now();

        // Không thể hủy vé khi đã quá thời gian khởi hành
        if ($now >= $departureTime) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy vé khi đã quá thời gian khởi hành'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái booking
            $booking->status = 'cancelled';
            $booking->save();

            // Cập nhật trạng thái các ticket
            Ticket::where('booking_id', $booking->id)
                ->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hủy đặt vé thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi hủy đặt vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái đặt vé (chỉ dành cho admin)
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

        $booking = Booking::findOrFail($id);

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái booking
            $booking->status = $request->status;
            $booking->save();

            // Cập nhật trạng thái các ticket
            Ticket::where('booking_id', $booking->id)
                ->update(['status' => $request->status]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đặt vé thành công',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi cập nhật trạng thái đặt vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Báo cáo doanh thu (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function revenueReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'type' => 'in:daily,monthly',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $query = Booking::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->type == 'daily') {
            $report = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('COUNT(*) as booking_count')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } else {
            $report = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('COUNT(*) as booking_count')
                )
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        }

        // Tính tổng doanh thu
        $totalRevenue = $report->sum('revenue');
        $totalBookingCount = $report->sum('booking_count');

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report,
                'total_revenue' => $totalRevenue,
                'total_booking_count' => $totalBookingCount,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'type' => $request->type
            ]
        ]);
    }
}
