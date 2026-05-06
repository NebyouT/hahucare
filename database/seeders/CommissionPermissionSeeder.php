<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommissionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for Commission module
        $permissions = [
            'view_commission',
            'add_commission', 
            'edit_commission',
            'delete_commission',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => true]);
        }

        // Assign permissions to roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $demo_admin = Role::firstOrCreate(['name' => 'demo_admin']);

        // Give all permissions to admin and demo_admin
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
        
        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }
    }
}
