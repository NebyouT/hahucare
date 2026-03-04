<?php

namespace Modules\Laboratory\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LaboratoryPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define all Laboratory module permissions
        $permissions = [
            // Labs
            'view_labs',
            'add_labs',
            'edit_labs',
            'delete_labs',
            
            // Lab Categories
            'view_lab_categories',
            'add_lab_categories',
            'edit_lab_categories',
            'delete_lab_categories',
            
            // Lab Services
            'view_lab_services',
            'add_lab_services',
            'edit_lab_services',
            'delete_lab_services',
            
            // Lab Results
            'view_lab_results',
            'add_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            
            // Lab Orders
            'view_lab_orders',
            'add_lab_orders',
            'edit_lab_orders',
            'delete_lab_orders',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    protected function assignPermissionsToRoles()
    {
        $allPermissions = [
            'view_labs',
            'add_labs',
            'edit_labs',
            'delete_labs',
            'view_lab_categories',
            'add_lab_categories',
            'edit_lab_categories',
            'delete_lab_categories',
            'view_lab_services',
            'add_lab_services',
            'edit_lab_services',
            'delete_lab_services',
            'view_lab_results',
            'add_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            'view_lab_orders',
            'add_lab_orders',
            'edit_lab_orders',
            'delete_lab_orders',
        ];

        // Admin gets all permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($allPermissions);
        }

        // Demo Admin gets all permissions
        $demoAdminRole = Role::where('name', 'demo_admin')->first();
        if ($demoAdminRole) {
            $demoAdminRole->givePermissionTo($allPermissions);
        }

        // Lab Technician role - Full CRUD permissions for their own lab data
        $technicianRole = Role::firstOrCreate(
            ['name' => 'lab_technician'],
            ['guard_name' => 'web', 'title' => 'Lab Technician']
        );
        $technicianRole->givePermissionTo([
            'view_labs',
            'edit_labs',
            'view_lab_categories',
            'view_lab_services',
            'add_lab_services',
            'edit_lab_services',
            'delete_lab_services',
            'view_lab_results',
            'add_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            'view_lab_orders',
            'add_lab_orders',
            'edit_lab_orders',
        ]);

        // Doctor can view tests and results
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorRole->givePermissionTo([
                'view_lab_tests',
                'view_lab_results',
                'create_lab_results',
            ]);
        }

        // Receptionist can view and create
        $receptionistRole = Role::where('name', 'receptionist')->first();
        if ($receptionistRole) {
            $receptionistRole->givePermissionTo([
                'view_lab_tests',
                'view_lab_results',
                'create_lab_results',
            ]);
        }
    }
}
