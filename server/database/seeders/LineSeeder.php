<?php
// database/seeders/LineSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Line;

class LineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lines = [
            [
                'departure' => 'Đà Nẵng',
                'destination' => 'Quảng Bình',
                'distance' => 300,
                'duration' => 240, // 4 giờ
                'base_price' => 300000,
                'description' => 'Tuyến đường ven biển đẹp, đi qua nhiều địa danh du lịch nổi tiếng.',
                'status' => 'active',
            ],
            [
                'departure' => 'Đà Nẵng',
                'destination' => 'Nghệ An',
                'distance' => 500,
                'duration' => 420, // 7 giờ
                'base_price' => 450000,
                'description' => 'Tuyến đường dài, đi qua nhiều tỉnh miền Trung.',
                'status' => 'active',
            ],
            [
                'departure' => 'Đà Nẵng',
                'destination' => 'Hà Giang',
                'distance' => 1000,
                'duration' => 840, // 14 giờ
                'base_price' => 650000,
                'description' => 'Tuyến đường dài đi từ miền Trung ra miền Bắc xa nhất.',
                'status' => 'active',
            ],
            [
                'departure' => 'Đà Nẵng',
                'destination' => 'Hồ Chí Minh',
                'distance' => 950,
                'duration' => 780, // 13 giờ
                'base_price' => 600000,
                'description' => 'Tuyến đường dài đi từ miền Trung vào miền Nam.',
                'status' => 'active',
            ],
            [
                'departure' => 'Quảng Bình',
                'destination' => 'Đà Nẵng',
                'distance' => 300,
                'duration' => 240, // 4 giờ
                'base_price' => 300000,
                'description' => 'Tuyến đường ven biển đẹp, đi qua nhiều địa danh du lịch nổi tiếng.',
                'status' => 'active',
            ],
            [
                'departure' => 'Nghệ An',
                'destination' => 'Đà Nẵng',
                'distance' => 500,
                'duration' => 420, // 7 giờ
                'base_price' => 450000,
                'description' => 'Tuyến đường dài, đi qua nhiều tỉnh miền Trung.',
                'status' => 'active',
            ],
            [
                'departure' => 'Hà Giang',
                'destination' => 'Đà Nẵng',
                'distance' => 1000,
                'duration' => 840, // 14 giờ
                'base_price' => 650000,
                'description' => 'Tuyến đường dài đi từ miền Bắc xa nhất về miền Trung.',
                'status' => 'active',
            ],
            [
                'departure' => 'Hồ Chí Minh',
                'destination' => 'Đà Nẵng',
                'distance' => 950,
                'duration' => 780, // 13 giờ
                'base_price' => 600000,
                'description' => 'Tuyến đường dài đi từ miền Nam ra miền Trung.',
                'status' => 'active',
            ],
        ];

        foreach ($lines as $line) {
            Line::create($line);
        }
    }
}
