<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LaboratoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for Laboratory module
        $permissions = [
            // Lab Tests
            'view_lab_tests',
            'create_lab_tests',
            'edit_lab_tests',
            'delete_lab_tests',
            
            // Lab Categories
            'view_lab_categories',
            'create_lab_categories',
            'edit_lab_categories',
            'delete_lab_categories',
            
            // Lab Results
            'view_lab_results',
            'create_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            
            // Lab Equipment
            'view_lab_equipment',
            'create_lab_equipment',
            'edit_lab_equipment',
            'delete_lab_equipment',
            
            // Labs
            'view_labs',
            'create_labs',
            'edit_labs',
            'delete_labs',
            
            // Lab Orders
            'view_lab_orders',
            'create_lab_orders',
            'edit_lab_orders',
            'delete_lab_orders',
            
            // Lab Test Ordering (from encounters)
            'order_lab_tests',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ], ['is_fixed' => true]);
        }

        // Assign permissions to roles
        $roles = [
            'admin' => $permissions,
            'demo_admin' => $permissions,
            'doctor' => [
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'view_lab_equipment',
                'view_labs',
                'view_lab_orders',
                'create_lab_orders',
                'edit_lab_orders',
                'order_lab_tests',
            ],
            'lab_technician' => [
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'create_lab_results',
                'edit_lab_results',
                'view_lab_equipment',
                'view_labs',
                'view_lab_orders',
                'edit_lab_orders',
            ],
            'receptionist' => [
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'view_labs',
                'view_lab_orders',
                'create_lab_orders',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }
}
