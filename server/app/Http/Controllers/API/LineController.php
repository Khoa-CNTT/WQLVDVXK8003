<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Line;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class LineController extends Controller
{
    /**
     * Hiển thị danh sách tuyến đường
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Cache::remember('lines.all', 3600, function () {
            return Line::with(['trips' => function ($query) {
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
        $cacheKey = 'lines.search.' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, 1800, function () use ($request) {
            return Line::query()
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

        $line = Line::create([
            'departure' => $request->departure,
            'destination' => $request->destination,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'base_price' => $request->base_price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        Cache::tags(['lines'])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Tạo tuyến đường thành công',
            'data' => $line
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
        return Cache::remember('lines.' . $id, 3600, function () use ($id) {
            return Line::with(['trips' => function ($query) {
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

        $line = Line::findOrFail($id);
        $line->update([
            'departure' => $request->departure,
            'destination' => $request->destination,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'base_price' => $request->base_price,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        Cache::tags(['lines'])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tuyến đường thành công',
            'data' => $line
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
        $line = Line::findOrFail($id);

        // Kiểm tra xem có chuyến xe nào đang sử dụng tuyến đường này không
        if ($line->trips()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tuyến đường này vì đang có chuyến xe sử dụng'
            ], 400);
        }

        $line->delete();

        Cache::tags(['lines'])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tuyến đường thành công'
        ]);
    }
}
