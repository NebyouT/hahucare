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
        $admin = Role::findByName('admin');
        $demo_admin = Role::findByName('demo_admin');
        $doctor = Role::findByName('doctor');
        $lab_technician = Role::findByName('lab_technician');
        $receptionist = Role::findByName('receptionist');

        // Give all permissions to admin and demo_admin
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
        
        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }

        // Give specific permissions to doctor
        if ($doctor) {
            $doctor->givePermissionTo([
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'view_lab_equipment',
                'view_labs',
                'view_lab_orders',
                'create_lab_orders',
                'edit_lab_orders',
                'order_lab_tests',
            ]);
        }

        // Give specific permissions to lab technician
        if ($lab_technician) {
            $lab_technician->givePermissionTo([
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'create_lab_results',
                'edit_lab_results',
                'view_lab_equipment',
                'view_labs',
                'view_lab_orders',
                'edit_lab_orders',
            ]);
        }

        // Give specific permissions to receptionist
        if ($receptionist) {
            $receptionist->givePermissionTo([
                'view_lab_tests',
                'view_lab_categories',
                'view_lab_results',
                'view_labs',
                'view_lab_orders',
                'create_lab_orders',
            ]);
        }
    }
}
