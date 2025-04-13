<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminRole = Role::where('slug', 'admin')->first();
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@phuongthanh.com',
            'password' => Hash::make('123456'),
            'phone' => '0905999999',
            'address' => 'Đà Nẵng'
        ]);
        $admin->roles()->attach($adminRole);

        // Create customer user
        $customerRole = Role::where('slug', 'customer')->first();
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('123456'),
            'phone' => '0905123456',
            'address' => 'Đà Nẵng'
        ]);
        $customer->roles()->attach($customerRole);
    }
}
