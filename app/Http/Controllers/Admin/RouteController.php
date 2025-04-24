<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Hiển thị danh sách tuyến đường
     */
    public function index()
    {
        $routes = Route::latest()->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }

    /**
     * Hiển thị form tạo tuyến đường mới
     */
    public function create()
    {
        return view('admin.routes.create');
    }

    /**
     * Lưu tuyến đường mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'distance' => 'required|numeric|min:1',
            'duration' => 'required|string',
            'fare' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Route::create([
            'departure' => $request->input('departure'),
            'destination' => $request->input('destination'),
            'distance' => $request->input('distance'),
            'duration' => $request->input('duration'),
            'fare' => $request->input('fare'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Tuyến đường đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết tuyến đường
     */
    public function show(Route $route)
    {
        return view('admin.routes.show', compact('route'));
    }

    /**
     * Hiển thị form chỉnh sửa tuyến đường
     */
    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    /**
     * Cập nhật thông tin tuyến đường
     */
    public function update(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'distance' => 'required|numeric|min:1',
            'duration' => 'required|string',
            'fare' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $route->update([
            'departure' => $request->input('departure'),
            'destination' => $request->input('destination'),
            'distance' => $request->input('distance'),
            'duration' => $request->input('duration'),
            'fare' => $request->input('fare'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', 'Tuyến đường đã được cập nhật thành công.');
    }

    /**
     * Xóa tuyến đường
     */
    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->route('admin.routes.index')
            ->with('success', 'Tuyến đường đã được xóa thành công.');
    }
}
