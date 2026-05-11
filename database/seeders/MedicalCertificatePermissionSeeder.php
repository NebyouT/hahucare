<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MedicalCertificatePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_medical_certificate',
            'add_medical_certificate',
            'edit_medical_certificate',
            'delete_medical_certificate',
            'print_medical_certificate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin and demo_admin
        $adminRoles = ['admin', 'demo_admin', 'doctor'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Assign limited permissions to receptionist
        $receptionist = Role::where('name', 'receptionist')->first();
        if ($receptionist) {
            $receptionist->givePermissionTo([
                'view_medical_certificate',
                'add_medical_certificate',
                'print_medical_certificate',
            ]);
        }
    }
}
