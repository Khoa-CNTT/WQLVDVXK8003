<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Trip;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VehicleController extends Controller
{
    /**
     * Hiển thị danh sách phương tiện
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo loại xe
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Tìm kiếm theo tên hoặc biển số
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->orderBy('name')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    /**
     * Lưu phương tiện mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles',
            'type' => 'required|in:standard,limousine,sleeper,vip,seater',
            'capacity' => 'required|integer|min:1',
            'manufacture_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'last_maintenance' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $vehicle = Vehicle::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tạo phương tiện thành công',
            'data' => $vehicle
        ], 201);
    }

    /**
     * Hiển thị chi tiết phương tiện
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    /**
     * Cập nhật thông tin phương tiện
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $id,
            'type' => 'required|in:standard,limousine,sleeper',
            'capacity' => 'required|integer|min:1',
            'manufacture_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'last_maintenance' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $vehicle = Vehicle::findOrFail($id);

        // Kiểm tra nếu phương tiện đang được sử dụng trong chuyến xe
        if ($request->status !== 'active' && $vehicle->status === 'active') {
            $activeTrips = Trip::where('vehicle_id', $id)
                              ->where('status', 'active')
                              ->where('departure_time', '>', now())
                              ->exists();

            if ($activeTrips) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi trạng thái phương tiện vì đang được sử dụng trong các chuyến xe sắp tới'
                ], 400);
            }
        }

        $vehicle->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phương tiện thành công',
            'data' => $vehicle
        ]);
    }

    /**
     * Xóa phương tiện
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Kiểm tra xem phương tiện đã được sử dụng trong trip nào chưa
        $usedInTrips = Trip::where('vehicle_id', $id)->exists();

        if ($usedInTrips) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa phương tiện này vì đã được sử dụng trong chuyến xe'
            ], 400);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa phương tiện thành công'
        ]);
    }

    /**
     * Hiển thị lịch sử bảo trì của phương tiện
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function maintenanceHistory($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Trong thực tế, bạn sẽ có một bảng riêng cho lịch sử bảo trì
        // Đây chỉ là một ví dụ đơn giản
        $maintenanceInfo = [
            'last_maintenance' => $vehicle->last_maintenance,
            'next_maintenance' => $vehicle->last_maintenance ? Carbon::parse($vehicle->last_maintenance)->addMonths(3)->format('Y-m-d') : null,
            'status' => $vehicle->status
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle' => $vehicle,
                'maintenance_info' => $maintenanceInfo
            ]
        ]);
    }

    /**
     * Cập nhật trạng thái xe (bảo trì, không hoạt động, hoạt động)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,maintenance,inactive',
            'maintenance_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $vehicle = Vehicle::findOrFail($id);

        // Kiểm tra nếu phương tiện đang được sử dụng trong chuyến xe
        if ($request->status !== 'active' && $vehicle->status === 'active') {
            $activeTrips = Trip::where('vehicle_id', $id)
                              ->where('status', 'active')
                              ->where('departure_time', '>', now())
                              ->exists();

            if ($activeTrips) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi trạng thái phương tiện vì đang được sử dụng trong các chuyến xe sắp tới'
                ], 400);
            }
        }

        $vehicle->status = $request->status;

        // Cập nhật ngày bảo trì gần nhất nếu chuyển sang bảo trì
        if ($request->status === 'maintenance') {
            $vehicle->last_maintenance = now();
        }

        $vehicle->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phương tiện thành công',
            'data' => $vehicle
        ]);
    }
}
