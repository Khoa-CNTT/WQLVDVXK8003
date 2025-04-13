<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Phương Thanh Express',
                'license_plate' => '43A-12345',
                'type' => 'standard',
                'capacity' => 30,
                'last_maintenance' => '2023-12-01',
                'is_active' => true,
                'amenities' => ['wifi', 'water', 'blanket', 'air_conditioning']
            ],
            [
                'name' => 'Xe Limousine Vip',
                'license_plate' => '43A-67890',
                'type' => 'limousine',
                'capacity' => 20,
                'last_maintenance' => '2023-12-15',
                'is_active' => true,
                'amenities' => ['wifi', 'water', 'blanket', 'air_conditioning', 'entertainment', 'snack']
            ],
            [
                'name' => 'Xe Giường Nằm Cao Cấp',
                'license_plate' => '43B-12345',
                'type' => 'sleeper',
                'capacity' => 24,
                'last_maintenance' => '2024-01-10',
                'is_active' => true,
                'amenities' => ['wifi', 'water', 'blanket', 'air_conditioning', 'entertainment']
            ],
            [
                'name' => 'Xe Khách Tiêu Chuẩn',
                'license_plate' => '43B-67890',
                'type' => 'standard',
                'capacity' => 35,
                'last_maintenance' => '2024-02-05',
                'is_active' => true,
                'amenities' => ['wifi', 'water', 'air_conditioning']
            ],
            [
                'name' => 'Xe VIP Đà Nẵng',
                'license_plate' => '43C-12345',
                'type' => 'vip',
                'capacity' => 16,
                'last_maintenance' => '2024-03-01',
                'is_active' => true,
                'amenities' => ['wifi', 'water', 'blanket', 'air_conditioning', 'entertainment', 'snack', 'massage_seat']
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
