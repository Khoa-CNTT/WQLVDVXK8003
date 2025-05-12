<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Hiển thị danh sách tuyến đường
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $routes = Route::paginate(20);

        return response()->json([
            'success' => true,
            'data' => $routes
        ]);
    }

    /**
     * Tìm kiếm tuyến đường
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string',
            'destination' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $routes = Route::where(function($query) use ($request) {
            $query->where('departure', 'like', '%' . $request->departure . '%')
                  ->where('destination', 'like', '%' . $request->destination . '%');
        })->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $routes
        ]);
    }

    /**
     * Lưu tuyến đường mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance' => 'required|numeric',
            'duration' => 'required|integer', // Thời gian di chuyển (phút)
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $route = Route::create([
            'departure' => $request->departure,
            'destination' => $request->destination,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'base_price' => $request->base_price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tuyến đường thành công',
            'data' => $route
        ], 201);
    }

    /**
     * Hiển thị chi tiết tuyến đường
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $route = Route::with('trips')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $route
        ]);
    }

    /**
     * Cập nhật thông tin tuyến đường
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance' => 'required|numeric',
            'duration' => 'required|integer',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $route = Route::findOrFail($id);
        $route->update([
            'departure' => $request->departure,
            'destination' => $request->destination,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'base_price' => $request->base_price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tuyến đường thành công',
            'data' => $route
        ]);
    }

    /**
     * Xóa tuyến đường
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $route = Route::findOrFail($id);

        // Kiểm tra xem có chuyến xe nào đang sử dụng tuyến đường này không
        if ($route->trips()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tuyến đường này vì đang có chuyến xe sử dụng'
            ], 400);
        }

        $route->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tuyến đường thành công'
        ]);
    }
}
