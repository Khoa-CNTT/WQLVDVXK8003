<?php
// database/seeders/DriverSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;

class DriverSeeder extends Seeder
{
    public function run()
    {
        // Tài xế mẫu - Nguyễn Văn Tài
        if (!Driver::where('phone', '0905111111')->exists()) {
            Driver::create([
                'name' => 'Nguyễn Văn Tài',
                'phone' => '0905111111',
                'email' => 'nvtai@gmail.com',
                'license_number' => 'B2-123456',
                'license_expiry' => '2027-05-15 13:43:40',
                'address' => 'Đà Nẵng',
                'birth_date' => '1990-05-15 00:00:00',
                'status' => 'active',
            ]);
        }

        // Các tài xế khác
        $drivers = [
            [
                'name' => 'Trần Văn Bình',
                'phone' => '0905222222',
                'email' => 'tvbinh@gmail.com',
                'license_number' => 'B2-234567',
                'license_expiry' => '2026-08-20',
                'address' => 'Huế',
                'birth_date' => '1988-07-22',
                'status' => 'active',
            ],
            [
                'name' => 'Lê Thị Hương',
                'phone' => '0905333333',
                'email' => 'lthuong@gmail.com',
                'license_number' => 'B2-345678',
                'license_expiry' => '2027-12-10',
                'address' => 'Quảng Nam',
                'birth_date' => '1992-03-15',
                'status' => 'active',
            ],
            // Thêm các tài xế khác nếu cần
        ];

        foreach ($drivers as $driverData) {
            // Kiểm tra xem tài xế đã tồn tại chưa (theo số điện thoại)
            if (!Driver::where('phone', $driverData['phone'])->exists()) {
                Driver::create($driverData);
            }
        }
    }
}
