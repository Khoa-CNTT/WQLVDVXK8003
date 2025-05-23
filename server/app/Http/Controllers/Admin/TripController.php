<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Line;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TripController extends Controller
{
    /**
     * Hiển thị danh sách chuyến xe
     */
    public function index()
    {
        $trips = Trip::with(['line', 'vehicle', 'driver'])
            ->latest()
            ->paginate(10);

        return view('admin.trips.index', compact('trips'));
    }

    /**
     * Hiển thị form tạo chuyến xe mới
     */
    public function create()
    {
        $lines = Line::where('status', 'active')->get();
        $vehicles = Vehicle::where('status', 'active')->get();
        $drivers = Driver::where('status', 'active')->get();

        return view('admin.trips.create', compact('lines', 'vehicles', 'drivers'));
    }

    /**
     * Lưu chuyến xe mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'line_id' => 'required|exists:lines,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_time' => 'required|date_format:Y-m-d H:i:s',
            'arrival_time' => 'required|date_format:Y-m-d H:i:s|after:departure_time',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['trip_code'] = 'TP' . time() . rand(1000, 9999);

        Trip::create($data);

        return redirect()->route('admin.trips.index')
            ->with('success', 'Chuyến xe đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết chuyến xe
     */
    public function show(Trip $trip)
    {
        $trip->load(['line', 'vehicle', 'driver', 'tickets.user']);
        return view('admin.trips.show', compact('trip'));
    }

    /**
     * Hiển thị form chỉnh sửa chuyến xe
     */
    public function edit(Trip $trip)
    {
        $lines = Line::where('status', 'active')->get();
        $vehicles = Vehicle::where('status', 'active')->get();
        $drivers = Driver::where('status', 'active')->get();

        return view('admin.trips.edit', compact('trip', 'lines', 'vehicles', 'drivers'));
    }

    /**
     * Cập nhật thông tin chuyến xe
     */
    public function update(Request $request, Trip $trip)
    {
        $validator = Validator::make($request->all(), [
            'line_id' => 'required|exists:lines,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_time' => 'required|date_format:Y-m-d H:i:s',
            'arrival_time' => 'required|date_format:Y-m-d H:i:s|after:departure_time',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $trip->update($request->all());

        return redirect()->route('admin.trips.index')
            ->with('success', 'Thông tin chuyến xe đã được cập nhật thành công.');
    }

    /**
     * Xóa chuyến xe
     */
    public function destroy(Trip $trip)
    {
        // Kiểm tra xem chuyến xe đã có vé nào chưa
        if ($trip->tickets()->count() > 0) {
            return redirect()->route('admin.trips.index')
                ->with('error', 'Không thể xóa chuyến xe này vì đã có người đặt vé.');
        }

        $trip->delete();

        return redirect()->route('admin.trips.index')
            ->with('success', 'Chuyến xe đã được xóa thành công.');
    }
}
