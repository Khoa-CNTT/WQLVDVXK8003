<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Hiển thị danh sách phương tiện
     */
    public function index()
    {
        $vehicles = Vehicle::latest()->paginate(10);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Hiển thị form tạo phương tiện mới
     */
    public function create()
    {
        return view('admin.vehicles.create');
    }

    /**
     * Lưu phương tiện mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|string|max:20|unique:vehicles',
            'type' => 'required|string|in:sleeper,seater,limousine,vip',
            'model' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive',
            'amenities' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('vehicles', 'public');
            $data['photo'] = $path;
        }

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Phương tiện đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết phương tiện
     */
    public function show(Vehicle $vehicle)
    {
        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Hiển thị form chỉnh sửa phương tiện
     */
    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.edit', compact('vehicle'));
    }

    /**
     * Cập nhật thông tin phương tiện
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicle->id,
            'type' => 'required|string|in:sleeper,seater,limousine,vip',
            'model' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive',
            'amenities' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Xóa ảnh cũ nếu có
            if ($vehicle->photo) {
                Storage::disk('public')->delete($vehicle->photo);
            }

            $path = $request->file('photo')->store('vehicles', 'public');
            $data['photo'] = $path;
        }

        $vehicle->update($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Thông tin phương tiện đã được cập nhật thành công.');
    }

    /**
     * Xóa phương tiện
     */
    public function destroy(Vehicle $vehicle)
    {
        // Xóa ảnh nếu có
        if ($vehicle->photo) {
            Storage::disk('public')->delete($vehicle->photo);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Phương tiện đã được xóa thành công.');
    }
}
