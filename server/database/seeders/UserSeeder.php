<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo tài khoản admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@phuongthanh.com',
            'phone' => '0905999999',
            'password' => Hash::make('admin123'),
            'role_id' => 1, // Admin role
            'status' => 'active',
        ]);

        // Tạo tài khoản khách hàng mẫu
        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@gmail.com',
            'phone' => '0912345678',
            'password' => Hash::make('customer123'),
            'role_id' => 2, // Customer role
            'status' => 'active',
        ]);

        // Tạo thêm 10 người dùng ngẫu nhiên
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Khách hàng ' . $i,
                'email' => 'customer' . $i . '@gmail.com',
                'phone' => '09' . rand(10000000, 99999999),
                'password' => Hash::make('password123'),
                'role_id' => 2, // Customer role
                'status' => 'active',
            ]);
        }
    }
}
