<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;

class BusController extends Controller
{
    // Lấy danh sách xe buýt
    public function index()
    {
        return response()->json(Bus::all());
    }

    // Thêm xe buýt mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'license_plate' => 'required|unique:buses',
            'seats' => 'required|integer',
            'company' => 'required'
        ]);

        $bus = Bus::create($request->all());
        return response()->json($bus, 201);
    }

    // Cập nhật thông tin xe buýt
    public function update(Request $request, Bus $bus)
    {
        $request->validate([
            'name' => 'required',
            'license_plate' => 'required|unique:buses,license_plate,' . $bus->id,
            'seats' => 'required|integer',
            'company' => 'required'
        ]);

        $bus->update($request->all());
        return response()->json($bus);
    }

    // Xóa xe buýt
    public function destroy(Bus $bus)
    {
        $bus->delete();
        return response()->json(['message' => 'Xe buýt đã được xóa']);
    }
}
