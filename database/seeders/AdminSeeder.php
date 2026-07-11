<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminRole = Role::findOrCreate('super-admin', 'admin');
        Role::findOrCreate('moderator', 'admin');

        $admin = Admin::query()->updateOrCreate(
            ['email' => 'admin@vivu.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        if (! $admin->hasRole('super-admin')) {
            $admin->assignRole($superAdminRole);
        }
    }
}
