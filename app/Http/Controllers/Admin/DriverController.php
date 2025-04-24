<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    /**
     * Hiển thị danh sách tài xế
     */
    public function index()
    {
        $drivers = Driver::latest()->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Hiển thị form tạo tài xế mới
     */
    public function create()
    {
        return view('admin.drivers.create');
    }

    /**
     * Lưu tài xế mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:drivers',
            'license_number' => 'required|string|max:50|unique:drivers',
            'license_class' => 'required|string|max:10',
            'license_expiry' => 'required|date',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive,on_leave',
            'experience_years' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('drivers', 'public');
            $data['photo'] = $path;
        }

        Driver::create($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Tài xế đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết tài xế
     */
    public function show(Driver $driver)
    {
        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Hiển thị form chỉnh sửa tài xế
     */
    public function edit(Driver $driver)
    {
        return view('admin.drivers.edit', compact('driver'));
    }

    /**
     * Cập nhật thông tin tài xế
     */
    public function update(Request $request, Driver $driver)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:drivers,phone,' . $driver->id,
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $driver->id,
            'license_class' => 'required|string|max:10',
            'license_expiry' => 'required|date',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive,on_leave',
            'experience_years' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Xóa ảnh cũ nếu có
            if ($driver->photo) {
                Storage::disk('public')->delete($driver->photo);
            }

            $path = $request->file('photo')->store('drivers', 'public');
            $data['photo'] = $path;
        }

        $driver->update($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Thông tin tài xế đã được cập nhật thành công.');
    }

    /**
     * Xóa tài xế
     */
    public function destroy(Driver $driver)
    {
        // Xóa ảnh nếu có
        if ($driver->photo) {
            Storage::disk('public')->delete($driver->photo);
        }

        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Tài xế đã được xóa thành công.');
    }
}
