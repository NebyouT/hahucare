<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FAQPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for FAQ module
        $permissions = [
            'view_faqs',
            'add_faqs', 
            'edit_faqs',
            'delete_faqs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => true]);
        }

        // Assign permissions to roles
        $admin = Role::findByName('admin');
        $demo_admin = Role::findByName('demo_admin');

        // Give all permissions to admin and demo_admin only
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
        
        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }

        // Note: Other roles (doctor, receptionist, etc.) do NOT get FAQ permissions
    }
}
