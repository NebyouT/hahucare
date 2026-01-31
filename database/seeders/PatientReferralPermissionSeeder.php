<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PatientReferralPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for Patient Referral module
        $permissions = [
            'view_patient_referral',
            'add_patient_referral', 
            'edit_patient_referral',
            'delete_patient_referral',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => true]);
        }

        // Assign permissions to roles
        $admin = Role::findByName('admin');
        $demo_admin = Role::findByName('demo_admin');
        $doctor = Role::findByName('doctor');

        // Give all permissions to admin and demo_admin
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
        
        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }

        // Give limited permissions to doctor
        if ($doctor) {
            $doctor->givePermissionTo([
                'view_patient_referral',
                'add_patient_referral',
                'edit_patient_referral',
            ]);
            // Doctors typically cannot delete referrals
        }
    }
}