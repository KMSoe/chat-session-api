<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@visibleone.com')->first();

        if (empty($user)) {
            $user = User::create([
                'email'    => 'admin@visibleone.com',
                'name'     => 'Admin',
                'password' => Hash::make("LaraTe@m"),
            ]);
        }

        $role = Role::where('name', 'Super Admin')->first();
        if (empty($role)) {
            $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api']);
        }
        $user->assignRole([$role->id]);
    }
}
