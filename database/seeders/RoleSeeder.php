<?php

namespace Database\Seeders;

use App\Models\CustomRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole       = CustomRole::firstOrCreate(['name' => "Super Admin", 'guard_name' => 'api']);
        $normalAdminRole = CustomRole::firstOrCreate(['name' => "Admin", 'guard_name' => 'api']);
        $employeeRole    = CustomRole::firstOrCreate(['name' => "Employee", 'guard_name' => 'api']);
    }
}
