<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo tài khoản admin nếu chưa tồn tại
        if (!User::where('email', 'admin@phuongthanh.com')->exists()) {
            User::create(attributes: [
                'name' => 'Admin',
                'email' => 'admin@phuongthanh.com',
                'phone' => '0905999999',
                'password' => Hash::make(value: 'admin123'),
                'role_id' => 1, // Admin role
                'status' => 'active',
            ]);
        }

        // Tạo tài khoản khách hàng mẫu nếu chưa tồn tại
        if (!User::where('email', 'nguyenvana@gmail.com')->exists()) {
            User::create(attributes: [
                'name' => 'Nguyễn Văn A',
                'email' => 'nguyenvana@gmail.com',
                'phone' => '0912345678',
                'password' => Hash::make(value: 'customer123'),
                'role_id' => 2, // Customer role
                'status' => 'active',
            ]);
        }

        // Tạo thêm 10 người dùng ngẫu nhiên
        for ($i = 1; $i <= 10; $i++) {
            $email = 'customer' . $i . '@gmail.com';

            // Kiểm tra email đã tồn tại chưa
            if (!User::where('email', $email)->exists()) {
                User::create(attributes: [
                    'name' => 'Khách hàng ' . $i,
                    'email' => $email,
                    'phone' => '09' . rand(min: 10000000, max: 99999999),
                    'password' => Hash::make(value: 'password123'),
                    'role_id' => 2, // Customer role
                    'status' => 'active',
                ]);
            }
        }
    }
}
