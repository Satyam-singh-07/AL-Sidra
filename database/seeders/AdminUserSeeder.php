<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        Role::firstOrCreate(['slug' => 'member'], ['name' => 'Member']);
        Role::firstOrCreate(['slug' => 'user'], ['name' => 'User']);

        $modules = config('modules') ?? [];
        foreach ($modules as $module) {
            RoleModule::firstOrCreate([
                'role_id' => $superAdminRole->id,
                'module'  => $module,
            ]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@alsidra.in'],
            [
                'name'     => 'Super Admin',
                'phone'    => '9999999999',
                'password' => Hash::make('password123'),
                'status'   => 'active',
            ]
        );

        if (!$admin->roles()->where('slug', 'super_admin')->exists()) {
            $admin->roles()->attach($superAdminRole->id);
        }
    }
}
