<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    /**
     * Hiển thị danh sách ghế
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seats = Seat::all();

        return response()->json([
            'success' => true,
            'data' => $seats
        ]);
    }

    /**
     * Lưu ghế mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|string|max:10',
            'seat_type' => 'required|in:normal,vip,sleeper',
            'position' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $seat = Seat::create([
            'seat_number' => $request->seat_number,
            'seat_type' => $request->seat_type,
            'position' => $request->position,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo ghế thành công',
            'data' => $seat
        ], 201);
    }

    /**
     * Hiển thị chi tiết ghế
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $seat = Seat::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $seat
        ]);
    }

    /**
     * Cập nhật thông tin ghế
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|string|max:10',
            'seat_type' => 'required|in:normal,vip,sleeper',
            'position' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $seat = Seat::findOrFail($id);
        $seat->update([
            'seat_number' => $request->seat_number,
            'seat_type' => $request->seat_type,
            'position' => $request->position,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ghế thành công',
            'data' => $seat
        ]);
    }

    /**
     * Xóa ghế
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $seat = Seat::findOrFail($id);

        // Kiểm tra xem ghế có được sử dụng trong vé nào không
        $ticketExists = Ticket::where('seat_id', $id)->exists();

        if ($ticketExists) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa ghế này vì đã được sử dụng trong vé'
            ], 400);
        }

        $seat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa ghế thành công'
        ]);
    }

    /**
     * Lấy danh sách ghế theo chuyến xe
     *
     * @param  int  $trip_id
     * @return \Illuminate\Http\Response
     */
    public function getTripSeats($trip_id)
    {
        $trip = Trip::with('vehicle')->findOrFail($trip_id);
        $vehicle = $trip->vehicle;

        // Lấy tất cả ghế theo loại xe
        $seats = Seat::where('status', 'active')->get();

        // Lấy danh sách ghế đã đặt
        $bookedSeats = Ticket::where('trip_id', $trip_id)
                            ->where('status', '!=', 'cancelled')
                            ->pluck('seat_id')
                            ->toArray();

        // Đánh dấu trạng thái ghế
        foreach ($seats as $seat) {
            if (in_array($seat->id, $bookedSeats)) {
                $seat->booking_status = 'booked';
            } else {
                $seat->booking_status = 'available';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trip' => $trip,
                'seats' => $seats
            ]
        ]);
    }
}
