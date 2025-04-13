<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Nguyễn Văn An',
                'license_number' => 'B2-123456',
                'phone' => '0905111111',
                'license_expiry' => '2027-01-15',
                'is_active' => true
            ],
            [
                'name' => 'Trần Văn Bình',
                'license_number' => 'D-789012',
                'phone' => '0905222222',
                'license_expiry' => '2026-06-20',
                'is_active' => true
            ],
            [
                'name' => 'Lê Văn Công',
                'license_number' => 'E-345678',
                'phone' => '0905333333',
                'license_expiry' => '2025-12-10',
                'is_active' => true
            ],
            [
                'name' => 'Phạm Văn Dũng',
                'license_number' => 'F-901234',
                'phone' => '0905444444',
                'license_expiry' => '2026-09-05',
                'is_active' => true
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
