<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;

class RouteController extends Controller
{
    // Lấy danh sách tuyến đường
    public function index()
    {
        return response()->json(Route::all());
    }

    // Thêm tuyến đường mới
    public function store(Request $request)
    {
        $request->validate([
            'start_location' => 'required',
            'end_location' => 'required',
            'price' => 'required|numeric',
            'departure_time' => 'required',
            'arrival_time' => 'required',
        ]);

        $route = Route::create($request->all());
        return response()->json($route, 201);
    }

    // Cập nhật thông tin tuyến đường
    public function update(Request $request, Route $route)
    {
        $request->validate([
            'start_location' => 'required',
            'end_location' => 'required',
            'price' => 'required|numeric',
            'departure_time' => 'required',
            'arrival_time' => 'required',
        ]);

        $route->update($request->all());
        return response()->json($route);
    }

    // Xóa tuyến đường
    public function destroy(Route $route)
    {
        $route->delete();
        return response()->json(['message' => 'Tuyến đường đã được xóa']);
    }
}
