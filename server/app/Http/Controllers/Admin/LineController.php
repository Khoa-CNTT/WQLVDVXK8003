<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Line;
use Illuminate\Support\Facades\Validator;

class LineController extends Controller
{
    /**
     * Hiển thị danh sách tuyến đường
     */
    public function index()
    {
        $lines = Line::latest()->paginate(10);
        return view('admin.lines.index', compact('lines'));
    }

    /**
     * Hiển thị form tạo tuyến đường mới
     */
    public function create()
    {
        return view('admin.lines.create');
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
            'base_price' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Line::create([
            'departure' => $request->input('departure'),
            'destination' => $request->input('destination'),
            'distance' => $request->input('distance'),
            'duration' => $request->input('duration'),
            'base_price' => $request->input('base_price'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.lines.index')
            ->with('success', 'Tuyến đường đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết tuyến đường
     */
    public function show(Line $line)
    {
        return view('admin.lines.show', compact('line'));
    }

    /**
     * Hiển thị form chỉnh sửa tuyến đường
     */
    public function edit(Line $line)
    {
        return view('admin.lines.edit', compact('line'));
    }

    /**
     * Cập nhật thông tin tuyến đường
     */
    public function update(Request $request, Line $line)
    {
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'distance' => 'required|numeric|min:1',
            'duration' => 'required|string',
            'base_price' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $line->update([
            'departure' => $request->input('departure'),
            'destination' => $request->input('destination'),
            'distance' => $request->input('distance'),
            'duration' => $request->input('duration'),
            'base_price' => $request->input('base_price'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.lines.index')
            ->with('success', 'Tuyến đường đã được cập nhật thành công.');
    }

    /**
     * Xóa tuyến đường
     */
    public function destroy(Line $line)
    {
        $line->delete();

        return redirect()->route('admin.lines.index')
            ->with('success', 'Tuyến đường đã được xóa thành công.');
    }
}
