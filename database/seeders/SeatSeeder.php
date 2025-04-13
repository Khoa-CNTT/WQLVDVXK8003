<?php

namespace Database\Seeders;

use App\Models\Seat;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {
            $seatCount = $vehicle->capacity;
            $seatTypes = ['window', 'aisle', 'middle'];

            for ($i = 1; $i <= $seatCount; $i++) {
                $position = $seatTypes[$i % count($seatTypes)];
                $type = ($i <= $seatCount / 5) ? 'vip' : 'standard';

                Seat::create([
                    'vehicle_id' => $vehicle->id,
                    'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'position' => $position,
                    'type' => $type,
                    'is_active' => true
                ]);
            }
        }
    }
}
