<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DriverController extends Controller
{
    /**
     * Hiển thị danh sách tài xế
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Driver::query();

        // Tìm kiếm theo tên
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('license_number', 'like', '%' . $search . '%');
        }

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sắp xếp
        $sortField = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $drivers = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $drivers
        ]);
    }

    /**
     * Lưu tài xế mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers',
            'email' => 'nullable|email|max:255|unique:drivers',
            'license_number' => 'required|string|max:50|unique:drivers',
            'license_expiry' => 'required|date|after:today',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date|before:18 years ago',
            'status' => 'required|in:active,inactive',
            'experience_years' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver = Driver::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'status' => $request->status,
            'experience_years' => $request->experience_years,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài xế thành công',
            'data' => $driver
        ], 201);
    }

    /**
     * Hiển thị chi tiết tài xế
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $driver = Driver::findOrFail($id);

        // Lấy lịch sử các chuyến xe của tài xế
        $trips = Trip::where('driver_id', $id)
                     ->with('line')
                     ->orderBy('departure_time', 'desc')
                     ->limit(10)
                     ->get();

        // Đếm số chuyến đã hoàn thành
        $completedTrips = Trip::where('driver_id', $id)
                            ->where('status', 'completed')
                            ->count();

        // Đếm số chuyến sắp tới
        $upcomingTrips = Trip::where('driver_id', $id)
                           ->where('status', 'active')
                           ->where('departure_time', '>', now())
                           ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => $driver,
                'recent_trips' => $trips,
                'stats' => [
                    'completed_trips' => $completedTrips,
                    'upcoming_trips' => $upcomingTrips
                ]
            ]
        ]);
    }

    /**
     * Cập nhật thông tin tài xế
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers,phone,' . $id,
            'email' => 'nullable|email|max:255|unique:drivers,email,' . $id,
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $id,
            'license_expiry' => 'required|date',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'experience_years' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'status' => $request->status,
            'experience_years' => $request->experience_years,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài xế thành công',
            'data' => $driver
        ]);
    }

    /**
     * Xóa hoặc vô hiệu hóa tài xế
     * Nếu có query param force=1 thì xóa cứng, ngược lại chỉ vô hiệu hóa
     */
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);

        // Kiểm tra xem tài xế có đang được gán cho chuyến xe nào không
        $tripExists = Trip::where('driver_id', $id)
                         ->where(function($query) {
                             $query->where('status', 'active')
                                  ->orWhere('status', 'pending');
                         })
                         ->exists();

        if ($tripExists) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài xế này vì đang được gán cho chuyến xe'
            ], 400);
        }

        // Nếu có query param force=1 thì xóa cứng
        if (request()->query('force') == 1) {
            $driver->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tài xế đã được xóa khỏi hệ thống'
            ]);
        } else {
            $driver->status = 'inactive';
            $driver->save();
            return response()->json([
                'success' => true,
                'message' => 'Tài xế đã được vô hiệu hóa thành công'
            ]);
        }
    }

    /**
     * Lấy danh sách tài xế có sẵn trong khoảng thời gian
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAvailableDrivers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        // Lấy danh sách ID tài xế đang bận trong khoảng thời gian
        $busyDriverIds = Trip::where('status', 'active')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('departure_time', [$startTime, $endTime])
                    ->orWhereBetween('arrival_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('departure_time', '<=', $startTime)
                          ->where('arrival_time', '>=', $endTime);
                    });
            })
            ->pluck('driver_id');

        // Lấy danh sách tài xế có sẵn
        $availableDrivers = Driver::where('status', 'active')
            ->whereNotIn('id', $busyDriverIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $availableDrivers
        ]);
    }
}
