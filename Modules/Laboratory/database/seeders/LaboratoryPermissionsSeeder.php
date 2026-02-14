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
            // Lab Tests
            'view_lab_tests',
            'create_lab_tests',
            'edit_lab_tests',
            'delete_lab_tests',
            'export_lab_tests',
            
            // Lab Results
            'view_lab_results',
            'create_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            'approve_lab_results',
            'print_lab_results',
            
            // Lab Categories
            'view_lab_categories',
            'create_lab_categories',
            'edit_lab_categories',
            'delete_lab_categories',
            
            // Lab Equipment
            'view_lab_equipment',
            'create_lab_equipment',
            'edit_lab_equipment',
            'delete_lab_equipment',
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
            'view_lab_tests',
            'create_lab_tests',
            'edit_lab_tests',
            'delete_lab_tests',
            'export_lab_tests',
            'view_lab_results',
            'create_lab_results',
            'edit_lab_results',
            'delete_lab_results',
            'approve_lab_results',
            'print_lab_results',
            'view_lab_categories',
            'create_lab_categories',
            'edit_lab_categories',
            'delete_lab_categories',
            'view_lab_equipment',
            'create_lab_equipment',
            'edit_lab_equipment',
            'delete_lab_equipment',
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

        // Lab Technician role
        $technicianRole = Role::firstOrCreate(
            ['name' => 'lab_technician'],
            ['guard_name' => 'web', 'title' => 'Lab Technician']
        );
        $technicianRole->givePermissionTo([
            'view_lab_tests',
            'view_lab_results',
            'create_lab_results',
            'edit_lab_results',
            'view_lab_equipment',
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
