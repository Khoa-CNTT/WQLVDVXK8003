<?php
// database/seeders/TripSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\Line;
use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Không xóa dữ liệu hiện có, chỉ thêm mới

        // Lấy dữ liệu
        $routes = Line::all();

        // Kiểm tra xem có tuyến nào không
        if ($routes->isEmpty()) {
            $this->command->info('Không có tuyến nào. Hãy chạy RouteSeeder trước.');
            return;
        }

        $vehicles = Vehicle::all();
        if ($vehicles->isEmpty()) {
            $this->command->info('Không có xe nào. Hãy chạy VehicleSeeder trước.');
            return;
        }

        $drivers = Driver::all();
        if ($drivers->isEmpty()) {
            $this->command->info('Không có tài xế nào. Hãy chạy DriverSeeder trước.');
            return;
        }

        // Tạo chuyến xe cho 7 ngày tới
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $currentDate = $startDate->copy();
        $tripCount = 0;

        $this->command->info('Bắt đầu tạo chuyến xe từ ' . $startDate->format('Y-m-d') . ' đến ' . $endDate->format('Y-m-d'));

        while ($currentDate <= $endDate) {
            // Tạo chuyến xe mỗi ngày cho mỗi tuyến
            foreach ($routes as $index => $route) {
                // Lấy xe và tài xế luân phiên
                $vehicle = $vehicles[$index % count($vehicles)];
                $driver = $drivers[$index % count($drivers)];

                // Tạo các khung giờ khởi hành
                $departureTimes = [
                    '05:00', '07:00', '09:00', '11:00', '13:00', '15:00', '17:00'
                ];

                foreach ($departureTimes as $timeIndex => $departureTime) {
                    $dateString = $currentDate->format('Y-m-d');
                    $departureDateTime = Carbon::parse("$dateString $departureTime");

                    // Kiểm tra xem chuyến đã tồn tại chưa
                    $existingTrip = Trip::where('line_id', $route->id)
                        ->where('departure_time', $departureDateTime)
                        ->first();

                    if ($existingTrip) {
                        continue; // Bỏ qua nếu đã tồn tại
                    }

                    // Tính giờ đến dựa trên thời gian di chuyển
                    $arrivalDateTime = $departureDateTime->copy()->addMinutes($route->duration);

                    // Tạo trip code duy nhất
                    $tripCode = 'TP' . strtoupper(Str::random(4)) . time() . rand(100, 999);

                    // Đảm bảo tripCode là duy nhất
                    while (Trip::where('trip_code', $tripCode)->exists()) {
                        $tripCode = 'TP' . strtoupper(Str::random(4)) . time() . rand(100, 999);
                    }

                    // Dao động giá 10%
                    $priceVariation = rand(-10, 10) / 100; // -10% to +10%
                    $price = $route->base_price * (1 + $priceVariation);
                    $price = round($price / 1000) * 1000; // Làm tròn đến 1000 VND

                    try {
                        Trip::create([
                            'line_id' => $route->id,
                            'vehicle_id' => $vehicle->id,
                            'driver_id' => $driver->id,
                            'departure_time' => $departureDateTime,
                            'arrival_time' => $arrivalDateTime,
                            'price' => $price,
                            'status' => 'active',
                            'trip_code' => $tripCode,
                            'notes' => null,
                        ]);
                        $tripCount++;
                    } catch (\Exception $e) {
                        $this->command->error('Lỗi khi tạo chuyến: ' . $e->getMessage());
                    }
                }
            }

            // Tăng ngày hiện tại lên 1
            $currentDate->addDay();
        }

        $this->command->info("Đã tạo thành công $tripCount chuyến xe mới.");
    }
}
