<?php
// database/seeders/TripSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy dữ liệu
        $routes = Route::all();
        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        // Tạo chuyến xe cho 30 ngày tới
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Tạo chuyến xe mỗi ngày cho mỗi tuyến
            foreach ($routes as $index => $route) {
                // Lấy xe và tài xế luân phiên
                $vehicle = $vehicles[$index % count($vehicles)];
                $driver = $drivers[$index % count($drivers)];

                // Tạo các khung giờ khởi hành
                $departureTimes = [
                    '06:00', '10:00', '14:00', '20:00'
                ];

                foreach ($departureTimes as $timeIndex => $departureTime) {
                    $dateString = $currentDate->format('Y-m-d');
                    $departureDateTime = Carbon::parse("$dateString $departureTime");

                    // Tính giờ đến dựa trên thời gian di chuyển
                    $arrivalDateTime = $departureDateTime->copy()->addMinutes($route->duration);

                    // Tạo trip code
                    $tripCode = 'TP' . time() . rand(1000, 9999);

                    // Dao động giá 10%
                    $priceVariation = rand(-10, 10) / 100; // -10% to +10%
                    $price = $route->base_price * (1 + $priceVariation);
                    $price = round($price / 1000) * 1000; // Làm tròn đến 1000 VND

                    Trip::create([
                        'route_id' => $route->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => $driver->id,
                        'departure_time' => $departureDateTime,
                        'arrival_time' => $arrivalDateTime,
                        'price' => $price,
                        'status' => 'active',
                        'trip_code' => $tripCode,
                        'notes' => null,
                    ]);
                }
            }

            // Tăng ngày hiện tại lên 1
            $currentDate->addDay();
        }
    }
}
