<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    /**
     * Display a listing of the trips.
     */
    public function index(Request $request)
    {
        $query = Trip::with(['route', 'vehicle', 'driver']);

        // Filter by date
        if ($request->has('date') && !empty($request->date)) {
            $query->where('departure_date', $request->date);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by route
        if ($request->has('route_id') && !empty($request->route_id)) {
            $query->where('route_id', $request->route_id);
        }

        $trips = $query->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->paginate(15);

        $routes = Route::orderBy('departure_location')->orderBy('arrival_location')->get();

        return view('admin.trips.index', compact('trips', 'routes'));
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create()
    {
        $routes = Route::where('is_active', true)->get();
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_active', true)->get();

        return view('admin.trips.create', compact('routes', 'vehicles', 'drivers'));
    }

    /**
     * Store a newly created trip.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_date' => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
            'price' => 'required|integer|min:0',
            'status' => 'required|in:scheduled,in-progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Calculate arrival date and time
        $route = Route::findOrFail($request->route_id);
        $departureDateTime = Carbon::parse($request->departure_date . ' ' . $request->departure_time);
        $arrivalDateTime = $departureDateTime->copy()->addMinutes($route->estimated_time);

        Trip::create([
            'route_id' => $request->route_id,
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'departure_date' => $request->departure_date,
            'departure_time' => $request->departure_time,
            'arrival_date' => $arrivalDateTime->format('Y-m-d'),
            'arrival_time' => $arrivalDateTime->format('H:i:s'),
            'price' => $request->price,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('trips.index')->with('success', 'Chuyến xe đã được tạo thành công');
    }

    /**
     * Display the specified trip.
     */
    public function show($id)
    {
        $trip = Trip::with(['route', 'vehicle', 'driver', 'tickets.seat', 'tickets.user'])
            ->findOrFail($id);

        return view('admin.trips.show', compact('trip'));
    }

    /**
     * Show the form for editing the specified trip.
     */
    public function edit($id)
    {
        $trip = Trip::findOrFail($id);
        $routes = Route::where('is_active', true)->get();
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_active', true)->get();

        return view('admin.trips.edit', compact('trip', 'routes', 'vehicles', 'drivers'));
    }

    /**
     * Update the specified trip.
     */
    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'departure_date' => 'required|date',
            'departure_time' => 'required',
            'price' => 'required|integer|min:0',
            'status' => 'required|in:scheduled,in-progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Calculate arrival date and time
        $route = Route::findOrFail($request->route_id);
        $departureDateTime = Carbon::parse($request->departure_date . ' ' . $request->departure_time);
        $arrivalDateTime = $departureDateTime->copy()->addMinutes($route->estimated_time);

        $trip->update([
            'route_id' => $request->route_id,
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'departure_date' => $request->departure_date,
            'departure_time' => $request->departure_time,
            'arrival_date' => $arrivalDateTime->format('Y-m-d'),
            'arrival_time' => $arrivalDateTime->format('H:i:s'),
            'price' => $request->price,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('trips.index')->with('success', 'Chuyến xe đã được cập nhật thành công');
    }

    /**
     * Remove the specified trip.
     */
    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);

        // Check if there are any tickets for this trip
        if ($trip->tickets()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa chuyến xe vì đã có vé được đặt');
        }

        $trip->delete();
        return redirect()->route('trips.index')->with('success', 'Chuyến xe đã được xóa thành công');
    }

    /**
     * Update trip status.
     */
    public function updateStatus(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        $request->validate([
            'status' => 'required|in:scheduled,in-progress,completed,cancelled',
        ]);

        $trip->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Trạng thái chuyến xe đã được cập nhật');
    }

    /**
     * Create multiple trips at once.
     */
    public function createBatch()
    {
        $routes = Route::where('is_active', true)->get();
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_active', true)->get();

        return view('admin.trips.create_batch', compact('routes', 'vehicles', 'drivers'));
    }

    /**
     * Store multiple trips.
     */
    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'departure_time' => 'required',
            'price' => 'required|integer|min:0',
            'days' => 'required|array',
            'days.*' => 'integer|between:0,6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $route = Route::findOrFail($request->route_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $departureTime = $request->departure_time;
        $days = $request->days;

        $createdCount = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Check if current day of week is selected
            if (in_array($currentDate->dayOfWeek, $days)) {
                $departureDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $departureTime);
                $arrivalDateTime = $departureDateTime->copy()->addMinutes($route->estimated_time);

                Trip::create([
                    'route_id' => $request->route_id,
                    'vehicle_id' => $request->vehicle_id,
                    'driver_id' => $request->driver_id,
                    'departure_date' => $currentDate->format('Y-m-d'),
                    'departure_time' => $departureTime,
                    'arrival_date' => $arrivalDateTime->format('Y-m-d'),
                    'arrival_time' => $arrivalDateTime->format('H:i:s'),
                    'price' => $request->price,
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                ]);

                $createdCount++;
            }

            $currentDate->addDay();
        }

        return redirect()->route('trips.index')->with('success', "Đã tạo $createdCount chuyến xe thành công");
    }
}
