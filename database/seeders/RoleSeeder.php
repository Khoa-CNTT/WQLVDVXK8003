<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra tên class chính xác
        // Nếu tên model là Role thì:
        Role::create([
            'name' => 'Admin',
            'slug' => 'admin'
        ]);

        Role::create([
            'name' => 'Customer',
            'slug' => 'customer'
        ]);

        // Nếu model có tên khác, chẳng hạn "Roles", hãy thay đổi:
        // Roles::create(...)
    }
}
