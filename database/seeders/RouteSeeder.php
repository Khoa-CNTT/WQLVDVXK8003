<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = [
            [
                'departure_location' => 'Đà Nẵng',
                'arrival_location' => 'Quảng Bình',
                'distance' => 280,
                'base_price' => 300000,
                'estimated_time' => 360, // 6 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Đà Nẵng',
                'arrival_location' => 'Nghệ An',
                'distance' => 480,
                'base_price' => 350000,
                'estimated_time' => 540, // 9 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Đà Nẵng',
                'arrival_location' => 'Hà Giang',
                'distance' => 1200,
                'base_price' => 500000,
                'estimated_time' => 1080, // 18 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Đà Nẵng',
                'arrival_location' => 'Hồ Chí Minh',
                'distance' => 980,
                'base_price' => 450000,
                'estimated_time' => 900, // 15 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Quảng Bình',
                'arrival_location' => 'Đà Nẵng',
                'distance' => 280,
                'base_price' => 300000,
                'estimated_time' => 360, // 6 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Nghệ An',
                'arrival_location' => 'Đà Nẵng',
                'distance' => 480,
                'base_price' => 350000,
                'estimated_time' => 540, // 9 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Hà Giang',
                'arrival_location' => 'Đà Nẵng',
                'distance' => 1200,
                'base_price' => 500000,
                'estimated_time' => 1080, // 18 hours
                'is_active' => true
            ],
            [
                'departure_location' => 'Hồ Chí Minh',
                'arrival_location' => 'Đà Nẵng',
                'distance' => 980,
                'base_price' => 450000,
                'estimated_time' => 900, // 15 hours
                'is_active' => true
            ],
        ];

        foreach ($routes as $route) {
            Route::create($route);
        }
    }
}
