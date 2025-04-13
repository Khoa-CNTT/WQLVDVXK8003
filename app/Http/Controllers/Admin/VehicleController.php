<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     */
    public function index()
    {
        $vehicles = Vehicle::orderBy('name')->paginate(10);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('admin.vehicles.create');
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles',
            'type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'last_maintenance' => 'required|date',
            'amenities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vehicle = Vehicle::create([
            'name' => $request->name,
            'license_plate' => $request->license_plate,
            'type' => $request->type,
            'capacity' => $request->capacity,
            'last_maintenance' => $request->last_maintenance,
            'is_active' => $request->has('is_active'),
            'amenities' => $request->amenities ?? [],
        ]);

        // Create seats for the vehicle
        $this->createSeatsForVehicle($vehicle);

        return redirect()->route('vehicles.index')->with('success', 'Phương tiện đã được tạo thành công');
    }

    /**
     * Display the specified vehicle.
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['seats', 'trips.route'])->findOrFail($id);
        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('admin.vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $id,
            'type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:100',
            'last_maintenance' => 'required|date',
            'amenities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldCapacity = $vehicle->capacity;
        $newCapacity = $request->capacity;

        $vehicle->update([
            'name' => $request->name,
            'license_plate' => $request->license_plate,
            'type' => $request->type,
            'capacity' => $newCapacity,
            'last_maintenance' => $request->last_maintenance,
            'is_active' => $request->has('is_active'),
            'amenities' => $request->amenities ?? [],
        ]);

        // Update seats if capacity changed
        if ($newCapacity != $oldCapacity) {
            $this->updateSeatsForVehicle($vehicle, $oldCapacity);
        }

        return redirect()->route('vehicles.index')->with('success', 'Phương tiện đã được cập nhật thành công');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if there are any trips assigned to this vehicle
        if ($vehicle->trips()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa phương tiện vì đã được gán cho các chuyến xe');
        }

        // Delete associated seats
        $vehicle->seats()->delete();

        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Phương tiện đã được xóa thành công');
    }

    /**
     * Create seats for a vehicle.
     */
    private function createSeatsForVehicle($vehicle)
    {
        $seatTypes = ['window', 'aisle', 'middle'];
        $capacity = $vehicle->capacity;

        for ($i = 1; $i <= $capacity; $i++) {
            $position = $seatTypes[$i % count($seatTypes)];
            $type = ($i <= $capacity / 5) ? 'vip' : 'standard';

            Seat::create([
                'vehicle_id' => $vehicle->id,
                'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'position' => $position,
                'type' => $type,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Update seats when vehicle capacity changes.
     */
    private function updateSeatsForVehicle($vehicle, $oldCapacity)
    {
        $newCapacity = $vehicle->capacity;

        if ($newCapacity > $oldCapacity) {
            // Add more seats
            $seatTypes = ['window', 'aisle', 'middle'];

            for ($i = $oldCapacity + 1; $i <= $newCapacity; $i++) {
                $position = $seatTypes[$i % count($seatTypes)];
                $type = ($i <= $newCapacity / 5) ? 'vip' : 'standard';

                Seat::create([
                    'vehicle_id' => $vehicle->id,
                    'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'position' => $position,
                    'type' => $type,
                    'is_active' => true,
                ]);
            }
        } else if ($newCapacity < $oldCapacity) {
            // Remove excess seats
            // First check if any of these seats are used in tickets
            $excessSeats = $vehicle->seats()
                ->orderByDesc('seat_number')
                ->limit($oldCapacity - $newCapacity)
                ->get();

            foreach ($excessSeats as $seat) {
                if ($seat->tickets()->where('status', '!=', 'cancelled')->exists()) {
                    // If seat is used, just mark it as inactive
                    $seat->update(['is_active' => false]);
                } else {
                    // If seat is not used, delete it
                    $seat->delete();
                }
            }
        }
    }
}
