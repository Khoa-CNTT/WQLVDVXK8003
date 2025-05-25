<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    /**
     * Tìm kiếm chuyến xe
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string',
            'destination' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Tìm các route phù hợp
        $departure = trim(mb_strtolower($request->departure));
        $destination = trim(mb_strtolower($request->destination));

        $lines = \App\Models\Line::whereRaw('LOWER(TRIM(departure)) = ?', [$departure])
                        ->whereRaw('LOWER(TRIM(destination)) = ?', [$destination])
                        ->where('status', 'active')
                        ->pluck('id');

        if ($lines->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy tuyến đường phù hợp',
                'data' => []
            ]);
        }

        // Tìm các trip trong ngày đã chọn
        $searchDate = Carbon::parse($request->date)->format('Y-m-d');

        $tripsQuery = Trip::with(['line', 'vehicle', 'driver'])
                      ->whereIn('line_id', $lines)
                      ->whereDate('departure_time', $searchDate)
                      ->where('status', 'active');

        // Nếu tìm kiếm ngày hôm nay thì chỉ lấy chuyến chưa khởi hành
        if ($searchDate === Carbon::now()->format('Y-m-d')) {
            $tripsQuery->where('departure_time', '>=', Carbon::now());
        }

        $trips = $tripsQuery->orderBy('departure_time')->get();

        // Lấy thông tin số ghế đã đặt cho mỗi chuyến
        foreach ($trips as $trip) {
            $bookedSeats = Ticket::where('trip_id', $trip->id)
                                 ->where('status', '!=', 'cancelled')
                                 ->count();

            $totalSeats = $trip->vehicle->capacity;
            $trip->available_seats = $totalSeats - $bookedSeats;
            $trip->total_seats = $totalSeats;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trips' => $trips,
                'search' => [
                    'departure' => $request->departure,
                    'destination' => $request->destination,
                    'date' => $searchDate
                ]
            ]
        ]);
    }

    /**
     * Tìm các tuyến đường phù hợp (tích hợp từ file ngoài server)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findRoutes(Request $request)
    {
        // Tìm các route phù hợp
        $departure = trim(mb_strtolower($request->departure));
        $destination = trim(mb_strtolower($request->destination));

        $lines = \App\Models\Line::whereRaw('LOWER(TRIM(departure)) = ?', [$departure])
                        ->whereRaw('LOWER(TRIM(destination)) = ?', [$destination])
                        ->where('status', 'active')
                        ->pluck('id');

        return response()->json(['lines' => $lines]);
    }

    /**
     * Hiển thị danh sách chuyến xe
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Trip::with(['line', 'vehicle', 'driver']);

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày
        if ($request->has('date')) {
            $query->whereDate('departure_time', $request->date);
        }

        // Lọc theo tuyến đường
        if ($request->has('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        $trips = $query->orderBy('departure_time')
                       ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $trips
        ]);
    }

    /**
     * Lưu chuyến xe mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'line_id' => 'required|exists:lines,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_time' => 'required|date_format:Y-m-d H:i:s',
            'arrival_time' => 'required|date_format:Y-m-d H:i:s|after:departure_time',
            'price' => 'required|numeric',
            'status' => 'required|in:active,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra xem xe và tài xế đã được sử dụng trong khoảng thời gian này chưa
        $departureTime = Carbon::parse($request->departure_time);
        $arrivalTime = Carbon::parse($request->arrival_time);

        // Kiểm tra xe đã được sử dụng chưa
        $vehicleConflict = Trip::where('vehicle_id', $request->vehicle_id)
            ->where('status', 'active')
            ->where(function($query) use ($departureTime, $arrivalTime) {
                $query->whereBetween('departure_time', [$departureTime, $arrivalTime])
                    ->orWhereBetween('arrival_time', [$departureTime, $arrivalTime])
                    ->orWhere(function($q) use ($departureTime, $arrivalTime) {
                        $q->where('departure_time', '<=', $departureTime)
                          ->where('arrival_time', '>=', $arrivalTime);
                    });
            })->exists();

        if ($vehicleConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Xe đã được sử dụng trong khoảng thời gian này'
            ], 400);
        }

        // Kiểm tra tài xế đã được phân công chưa
        $driverConflict = Trip::where('driver_id', $request->driver_id)
            ->where('status', 'active')
            ->where(function($query) use ($departureTime, $arrivalTime) {
                $query->whereBetween('departure_time', [$departureTime, $arrivalTime])
                    ->orWhereBetween('arrival_time', [$departureTime, $arrivalTime])
                    ->orWhere(function($q) use ($departureTime, $arrivalTime) {
                        $q->where('departure_time', '<=', $departureTime)
                          ->where('arrival_time', '>=', $arrivalTime);
                    });
            })->exists();

        if ($driverConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Tài xế đã được phân công trong khoảng thời gian này'
            ], 400);
        }

        $trip = Trip::create([
            'line_id' => $request->line_id,
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price' => $request->price,
            'status' => $request->status,
            'notes' => $request->notes,
            'trip_code' => 'TP' . time() . rand(1000, 9999),
        ]);

        // Load relationships
        $trip->load(['line', 'vehicle', 'driver']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo chuyến xe thành công',
            'data' => $trip
        ], 201);
    }

    /**
     * Hiển thị chi tiết chuyến xe
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $trip = Trip::with(['line', 'vehicle', 'driver'])->findOrFail($id);

        // Lấy thông tin số ghế đã đặt
        $bookedSeats = Ticket::where('trip_id', $trip->id)
                             ->where('status', '!=', 'cancelled')
                             ->count();

        $totalSeats = $trip->vehicle->capacity;
        $trip->available_seats = $totalSeats - $bookedSeats;
        $trip->total_seats = $totalSeats;

        return response()->json([
            'success' => true,
            'data' => $trip
        ]);
    }

    /**
     * Cập nhật thông tin chuyến xe
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'line_id' => 'required|exists:lines,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_time' => 'required|date_format:Y-m-d H:i:s',
            'arrival_time' => 'required|date_format:Y-m-d H:i:s|after:departure_time',
            'price' => 'required|numeric',
            'status' => 'required|in:active,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $trip = Trip::findOrFail($id);

        // Kiểm tra nếu chuyến đã có vé đặt và cần thay đổi xe hoặc tài xế
        $hasBookings = Ticket::where('trip_id', $id)
                           ->where('status', '!=', 'cancelled')
                           ->exists();

        if ($hasBookings) {
            // Chỉ cho phép thay đổi trạng thái và ghi chú nếu đã có vé đặt
            $trip->update([
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chỉ cập nhật trạng thái và ghi chú vì chuyến đã có vé đặt',
                'data' => $trip
            ]);
        }

        // Kiểm tra xung đột xe và tài xế
        $departureTime = Carbon::parse($request->departure_time);
        $arrivalTime = Carbon::parse($request->arrival_time);

        // Kiểm tra xe đã được sử dụng chưa
        $vehicleConflict = Trip::where('vehicle_id', $request->vehicle_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->where(function($query) use ($departureTime, $arrivalTime) {
                $query->whereBetween('departure_time', [$departureTime, $arrivalTime])
                    ->orWhereBetween('arrival_time', [$departureTime, $arrivalTime])
                    ->orWhere(function($q) use ($departureTime, $arrivalTime) {
                        $q->where('departure_time', '<=', $departureTime)
                          ->where('arrival_time', '>=', $arrivalTime);
                    });
            })->exists();

        if ($vehicleConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Xe đã được sử dụng trong khoảng thời gian này'
            ], 400);
        }

        // Kiểm tra tài xế đã được phân công chưa
        $driverConflict = Trip::where('driver_id', $request->driver_id)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->where(function($query) use ($departureTime, $arrivalTime) {
                $query->whereBetween('departure_time', [$departureTime, $arrivalTime])
                    ->orWhereBetween('arrival_time', [$departureTime, $arrivalTime])
                    ->orWhere(function($q) use ($departureTime, $arrivalTime) {
                        $q->where('departure_time', '<=', $departureTime)
                          ->where('arrival_time', '>=', $arrivalTime);
                    });
            })->exists();

        if ($driverConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Tài xế đã được phân công trong khoảng thời gian này'
            ], 400);
        }

        $trip->update([
            'line_id' => $request->line_id,
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price' => $request->price,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Load relationships
        $trip->load(['line', 'vehicle', 'driver']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chuyến xe thành công',
            'data' => $trip
        ]);
    }

    /**
     * Xóa chuyến xe
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);

        // Kiểm tra xem chuyến có vé đã đặt không
        $hasBookings = Ticket::where('trip_id', $id)
                           ->where('status', '!=', 'cancelled')
                           ->exists();

        if ($hasBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa chuyến xe này vì đã có vé được đặt'
            ], 400);
        }

        $trip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa chuyến xe thành công'
        ]);
    }

    /**
     * Báo cáo chuyến xe (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tripReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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

        // Thống kê số chuyến theo tuyến
        $lineStats = Trip::whereBetween('departure_time', [$startDate, $endDate])
            ->select('line_id', DB::raw('COUNT(*) as trip_count'))
            ->groupBy('line_id')
            ->with('line')
            ->get();

        // Thống kê số vé đã bán theo chuyến
        $tripStats = Trip::whereBetween('departure_time', [$startDate, $endDate])
            ->with(['line', 'tickets' => function($query) {
                $query->where('status', '!=', 'cancelled');
            }])
            ->get()
            ->map(function($trip) {
                return [
                    'trip_id' => $trip->id,
                    'trip_code' => $trip->trip_code,
                    'line' => $trip->line ? ($trip->line->departure . ' - ' . $trip->line->destination) : null,
                    'departure_time' => $trip->departure_time,
                    'status' => $trip->status,
                    'ticket_count' => $trip->tickets->count(),
                    'revenue' => $trip->tickets->count() * $trip->price
                ];
            });

        // Thống kê tổng hợp
        $totalTrips = Trip::whereBetween('departure_time', [$startDate, $endDate])->count();
        $totalTickets = Ticket::whereHas('trip', function($query) use ($startDate, $endDate) {
                $query->whereBetween('departure_time', [$startDate, $endDate]);
            })
            ->where('status', '!=', 'cancelled')
            ->count();
        $totalRevenue = $tripStats->sum('revenue');

        return response()->json([
            'success' => true,
            'data' => [
                'line_stats' => $lineStats,
                'trip_stats' => $tripStats,
                'summary' => [
                    'total_trips' => $totalTrips,
                    'total_tickets' => $totalTickets,
                    'total_revenue' => $totalRevenue
                ],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
