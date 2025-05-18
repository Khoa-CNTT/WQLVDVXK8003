<?php
// database/seeders/VehicleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use Carbon\Carbon;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicles = [
            [
                'name' => 'Xe Limousine 01',
                'license_plate' => '43A-12345',
                'type' => 'limousine',
                'capacity' => 12,
                'manufacture_year' => 2022,
                'last_maintenance' => Carbon::now()->subMonth(),
                'status' => 'active',
                'description' => 'Xe Limousine 12 chỗ cao cấp, đầy đủ tiện nghi.',
            ],
            [
                'name' => 'Xe Limousine 02',
                'license_plate' => '43A-23456',
                'type' => 'limousine',
                'capacity' => 12,
                'manufacture_year' => 2021,
                'last_maintenance' => Carbon::now()->subMonths(2),
                'status' => 'active',
                'description' => 'Xe Limousine 12 chỗ cao cấp, đầy đủ tiện nghi.',
            ],
            [
                'name' => 'Xe Giường Nằm 01',
                'license_plate' => '43A-34567',
                'type' => 'sleeper',
                'capacity' => 40,
                'manufacture_year' => 2023,
                'last_maintenance' => Carbon::now()->subWeeks(2),
                'status' => 'active',
                'description' => 'Xe giường nằm 40 chỗ cao cấp, tiện nghi đầy đủ.',
            ],
            [
                'name' => 'Xe Giường Nằm 02',
                'license_plate' => '43A-45678',
                'type' => 'sleeper',
                'capacity' => 40,
                'manufacture_year' => 2022,
                'last_maintenance' => Carbon::now()->subWeeks(3),
                'status' => 'active',
                'description' => 'Xe giường nằm 40 chỗ cao cấp, tiện nghi đầy đủ.',
            ],
            [
                'name' => 'Xe Tiêu Chuẩn 01',
                'license_plate' => '43A-56789',
                'type' => 'standard',
                'capacity' => 30,
                'manufacture_year' => 2020,
                'last_maintenance' => Carbon::now()->subWeeks(1),
                'status' => 'active',
                'description' => 'Xe khách tiêu chuẩn 30 chỗ.',
            ],
        ];

        $count = 0;
        foreach ($vehicles as $vehicle) {
            // Kiểm tra xem xe đã tồn tại chưa dựa trên biển số
            if (!Vehicle::where('license_plate', $vehicle['license_plate'])->exists()) {
                Vehicle::create($vehicle);
                $count++;
            }
        }

        $this->command->info("Đã tạo $count xe mới.");
    }
}
