<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    /**
     * Display a listing of the drivers.
     */
    public function index()
    {
        $drivers = Driver::orderBy('name')->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver.
     */
    public function create()
    {
        return view('admin.drivers.create');
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:drivers',
            'phone' => 'required|string|max:20',
            'license_expiry' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Driver::create([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'phone' => $request->phone,
            'license_expiry' => $request->license_expiry,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('drivers.index')->with('success', 'Tài xế đã được tạo thành công');
    }

    /**
     * Display the specified driver.
     */
    public function show($id)
    {
        $driver = Driver::with('trips.route')->findOrFail($id);
        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return view('admin.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver.
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:drivers,license_number,' . $id,
            'phone' => 'required|string|max:20',
            'license_expiry' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $driver->update([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'phone' => $request->phone,
            'license_expiry' => $request->license_expiry,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('drivers.index')->with('success', 'Tài xế đã được cập nhật thành công');
    }

    /**
     * Remove the specified driver.
     */
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);

        // Check if there are any trips assigned to this driver
        if ($driver->trips()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa tài xế vì đã được gán cho các chuyến xe');
        }

        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Tài xế đã được xóa thành công');
    }
}
