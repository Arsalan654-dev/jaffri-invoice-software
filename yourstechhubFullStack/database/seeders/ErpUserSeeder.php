<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ErpUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('DELETE FROM user_permissions');
        DB::statement('DELETE FROM role_users');
        User::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@yourstechhub.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'department' => 'Administration',
                'designation' => 'System Administrator',
                'employee_id' => 'EMP001',
                'is_active' => true,
            ],
            [
                'name' => 'Finance User',
                'email' => 'finance@yourstechhub.com',
                'password' => Hash::make('password'),
                'role' => 'finance',
                'department' => 'Finance',
                'designation' => 'Finance Manager',
                'employee_id' => 'EMP002',
                'is_active' => true,
            ],
            [
                'name' => 'Sales User',
                'email' => 'sales@yourstechhub.com',
                'password' => Hash::make('password'),
                'role' => 'sales',
                'department' => 'Sales',
                'designation' => 'Sales Executive',
                'employee_id' => 'EMP003',
                'is_active' => true,
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@yourstechhub.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'department' => 'Operations',
                'designation' => 'Staff Member',
                'employee_id' => 'EMP004',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);

            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->attach($role->id);

                if ($roleSlug !== 'super_admin') {
                    foreach ($role->permissions as $permission) {
                        $user->userPermissions()->create([
                            'permission_id' => $permission->id,
                            'type' => 'allow',
                        ]);
                    }
                }
            }
        }
    }
}
