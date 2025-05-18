<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class RouteController extends Controller
{
    /**
     * Hiển thị danh sách tuyến đường
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Cache::remember('routes.all', 3600, function () {
            return Route::with(['trips' => function ($query) {
                $query->whereDate('departure_time', '>=', now());
            }])->get();
        });
    }

    /**
     * Tìm kiếm tuyến đường
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $cacheKey = 'routes.search.' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, 1800, function () use ($request) {
            return Route::query()
                ->when($request->from, function ($query, $from) {
                    return $query->where('departure_location', 'like', "%{$from}%");
                })
                ->when($request->to, function ($query, $to) {
                    return $query->where('arrival_location', 'like', "%{$to}%");
                })
                ->with(['trips' => function ($query) {
                    $query->whereDate('departure_time', '>=', now())
                          ->orderBy('departure_time');
                }])
                ->get();
        });
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

        Cache::tags(['routes'])->flush();

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
        return Cache::remember('routes.' . $id, 3600, function () use ($id) {
            return Route::with(['trips' => function ($query) {
                $query->whereDate('departure_time', '>=', now())
                      ->orderBy('departure_time');
            }])->findOrFail($id);
        });
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

        Cache::tags(['routes'])->flush();

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

        Cache::tags(['routes'])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tuyến đường thành công'
        ]);
    }
}
