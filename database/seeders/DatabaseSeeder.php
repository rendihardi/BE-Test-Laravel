<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // 2. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('konfirmasi'),
            ]
        );
        $admin->syncRoles($adminRole);

        // 3. Create Staff User
        $staff = User::firstOrCreate(
            ['email' => 'staf@gmail.com'],
            [
                'name' => 'Staff',
                'password' => Hash::make('konfirmasi'),
            ]
        );
        $staff->syncRoles($staffRole);
    }
}
