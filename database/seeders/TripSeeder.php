<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = Route::all();
        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        // Generate trips for the next 7 days
        for ($day = 0; $day < 7; $day++) {
            $departureDate = Carbon::now()->addDays($day)->format('Y-m-d');

            foreach ($routes as $index => $route) {
                // Morning trip
                $vehicle = $vehicles[$index % count($vehicles)];
                $driver = $drivers[$index % count($drivers)];

                $departureTime = '06:00:00';
                $arrivalDate = $departureDate;
                $arrivalTime = Carbon::parse($departureTime)->addMinutes($route->estimated_time)->format('H:i:s');

                // If arrival time is on the next day
                if (Carbon::parse($arrivalTime)->format('H') < Carbon::parse($departureTime)->format('H')) {
                    $arrivalDate = Carbon::parse($departureDate)->addDay()->format('Y-m-d');
                }

                Trip::create([
                    'route_id' => $route->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'departure_date' => $departureDate,
                    'departure_time' => $departureTime,
                    'arrival_date' => $arrivalDate,
                    'arrival_time' => $arrivalTime,
                    'price' => $route->base_price + (($day % 3) * 50000), // Price varies by day
                    'status' => 'scheduled',
                    'notes' => 'Chuyến sáng'
                ]);

                // Afternoon trip
                $vehicle = $vehicles[($index + 1) % count($vehicles)];
                $driver = $drivers[($index + 1) % count($drivers)];

                $departureTime = '14:00:00';
                $arrivalDate = $departureDate;
                $arrivalTime = Carbon::parse($departureTime)->addMinutes($route->estimated_time)->format('H:i:s');

                // If arrival time is on the next day
                if (Carbon::parse($arrivalTime)->format('H') < Carbon::parse($departureTime)->format('H')) {
                    $arrivalDate = Carbon::parse($departureDate)->addDay()->format('Y-m-d');
                }

                Trip::create([
                    'route_id' => $route->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'departure_date' => $departureDate,
                    'departure_time' => $departureTime,
                    'arrival_date' => $arrivalDate,
                    'arrival_time' => $arrivalTime,
                    'price' => $route->base_price + (($day % 3) * 50000) + 20000, // Afternoon is a bit more expensive
                    'status' => 'scheduled',
                    'notes' => 'Chuyến chiều'
                ]);

                // Evening trip (only for some routes)
                if ($index % 2 == 0) {
                    $vehicle = $vehicles[($index + 2) % count($vehicles)];
                    $driver = $drivers[($index + 2) % count($drivers)];

                    $departureTime = '20:00:00';
                    $arrivalDate = Carbon::parse($departureDate)->addDay()->format('Y-m-d');
                    $arrivalTime = Carbon::parse($departureTime)->addMinutes($route->estimated_time)->format('H:i:s');

                    Trip::create([
                        'route_id' => $route->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => $driver->id,
                        'departure_date' => $departureDate,
                        'departure_time' => $departureTime,
                        'arrival_date' => $arrivalDate,
                        'arrival_time' => $arrivalTime,
                        'price' => $route->base_price + (($day % 3) * 50000) - 30000, // Night time discount
                        'status' => 'scheduled',
                        'notes' => 'Chuyến tối'
                    ]);
                }
            }
        }
    }
}
