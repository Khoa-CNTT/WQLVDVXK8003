<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display the search form.
     */
    public function searchForm()
    {
        $routes = Route::where('is_active', true)->get();
        return view('booking.search', compact('routes'));
    }

    /**
     * Search for available trips.
     */
    public function search(Request $request)
    {
        $request->validate([
            'departure' => 'required|string',
            'destination' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $route = Route::where('departure_location', $request->departure)
            ->where('arrival_location', $request->destination)
            ->where('is_active', true)
            ->first();

        if (!$route) {
            return back()->withErrors(['message' => 'Route not found'])->withInput();
        }

        $trips = Trip::where('route_id', $route->id)
            ->where('departure_date', $request->date)
            ->where('status', 'scheduled')
            ->with(['vehicle', 'driver'])
            ->get();

        return view('booking-results', [
            'trips' => $trips,
            'departure' => $request->departure,
            'destination' => $request->destination,
            'date' => $request->date,
        ]);
    }

    /**
     * Display available seats for a trip.
     */
    public function seats(Request $request, $tripId)
    {
        $trip = Trip::with(['route', 'vehicle', 'vehicle.seats'])
            ->findOrFail($tripId);

        // Get available seats
        $availableSeats = $trip->availableSeats();

        return view('booking.seats', [
            'trip' => $trip,
            'availableSeats' => $availableSeats,
        ]);
    }

    /**
     * Display passenger details form.
     */
    public function passengerDetails(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'exists:seats,id',
        ]);

        $trip = Trip::with(['route', 'vehicle'])->findOrFail($request->trip_id);
        $seatIds = $request->seat_ids;

        return view('ticket-detail', [
            'trip' => $trip,
            'seatIds' => $seatIds,
        ]);
    }
}
