<?php
// database/seeders/SeatSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\Vehicle;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo ghế cho xe Limousine 12 chỗ
        $seats = [
            // Tầng 1
            ['seat_number' => 'A01', 'seat_type' => 'vip', 'position' => 1],
            ['seat_number' => 'A02', 'seat_type' => 'vip', 'position' => 2],
            ['seat_number' => 'A03', 'seat_type' => 'vip', 'position' => 3],
            ['seat_number' => 'A04', 'seat_type' => 'vip', 'position' => 4],
            ['seat_number' => 'A05', 'seat_type' => 'vip', 'position' => 5],
            ['seat_number' => 'A06', 'seat_type' => 'vip', 'position' => 6],
            // Tầng 2
            ['seat_number' => 'B01', 'seat_type' => 'vip', 'position' => 7],
            ['seat_number' => 'B02', 'seat_type' => 'vip', 'position' => 8],
            ['seat_number' => 'B03', 'seat_type' => 'vip', 'position' => 9],
            ['seat_number' => 'B04', 'seat_type' => 'vip', 'position' => 10],
            ['seat_number' => 'B05', 'seat_type' => 'vip', 'position' => 11],
            ['seat_number' => 'B06', 'seat_type' => 'vip', 'position' => 12],
        ];

        foreach ($seats as $seat) {
            Seat::create($seat);
        }

        // Tạo ghế cho xe Giường nằm 40 chỗ
        $seats = [];
        for ($i = 1; $i <= 20; $i++) {
            $seats[] = [
                'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'seat_type' => 'sleeper',
                'position' => $i,
                'status' => 'active',
            ];
        }

        for ($i = 1; $i <= 20; $i++) {
            $seats[] = [
                'seat_number' => 'B' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'seat_type' => 'sleeper',
                'position' => $i + 20,
                'status' => 'active',
            ];
        }

        foreach ($seats as $seat) {
            Seat::create($seat);
        }

        // Tạo ghế cho xe Tiêu chuẩn 30 chỗ
        $seats = [];
        for ($i = 1; $i <= 30; $i++) {
            $seats[] = [
                'seat_number' => 'C' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'seat_type' => 'normal',
                'position' => $i,
                'status' => 'active',
            ];
        }

        foreach ($seats as $seat) {
            Seat::create($seat);
        }
    }
}
