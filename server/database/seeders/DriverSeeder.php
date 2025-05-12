<?php
// database/seeders/DriverSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use Carbon\Carbon;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drivers = [
            [
                'name' => 'Nguyễn Văn Tài',
                'phone' => '0905111111',
                'email' => 'nvtai@gmail.com',
                'license_number' => 'B2-123456',
                'license_expiry' => Carbon::now()->addYears(2),
                'address' => 'Đà Nẵng',
                'birth_date' => '1990-05-15',
                'status' => 'active',
            ],
            [
                'name' => 'Trần Văn Lâm',
                'phone' => '0905222222',
                'email' => 'tvlam@gmail.com',
                'license_number' => 'D-234567',
                'license_expiry' => Carbon::now()->addYears(3),
                'address' => 'Huế',
                'birth_date' => '1985-08-20',
                'status' => 'active',
            ],
            [
                'name' => 'Lê Anh Tuấn',
                'phone' => '0905333333',
                'email' => 'latuan@gmail.com',
                'license_number' => 'D-345678',
                'license_expiry' => Carbon::now()->addYears(1),
                'address' => 'Quảng Nam',
                'birth_date' => '1988-12-10',
                'status' => 'active',
            ],
            [
                'name' => 'Phạm Minh Đức',
                'phone' => '0905444444',
                'email' => 'pmduc@gmail.com',
                'license_number' => 'E-456789',
                'license_expiry' => Carbon::now()->addYears(2),
                'address' => 'Đà Nẵng',
                'birth_date' => '1992-03-25',
                'status' => 'active',
            ],
            [
                'name' => 'Hoàng Nhật Nam',
                'phone' => '0905555555',
                'email' => 'hnnam@gmail.com',
                'license_number' => 'D-567890',
                'license_expiry' => Carbon::now()->addYears(3),
                'address' => 'Quảng Trị',
                'birth_date' => '1987-06-18',
                'status' => 'active',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
