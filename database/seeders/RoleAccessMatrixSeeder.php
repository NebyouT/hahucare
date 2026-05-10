<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RoleAccessMatrixSeeder
 *
 * Applies the staff dashboard access matrix from the spec.
 * Roles considered:
 *   - admin / demo_admin  : Super Admin (full access)
 *   - vendor              : Clinic Admin
 *   - doctor              : Doctor
 *   - receptionist        : Receptionist
 *   - pharma              : Pharmacist
 *   - lab_technician      : Lab Technologist
 *
 * Note: Scope-based filtering (Own Clinics / Self / Limited) is enforced
 * at the controller/query layer, not at the permission level.
 */
class RoleAccessMatrixSeeder extends Seeder
{
    public function run(): void
    {
        // Section => permission base slugs (will expand to view/add/edit/delete)
        $crudSections = [
            'appointment'           => ['clinic_appointment_list'],
            'bed_management'        => ['bed_master', 'allocations'],
            'encounter'             => ['encounter'],
            'doctors'               => ['doctors'],
            'doctor_session'        => ['doctors_session'],
            'clinic'                => ['clinics_center'],
            'categories'            => ['clinics_category'],
            'service'               => ['clinics_service'],
            'review'                => ['reviews'],
            'patient'               => ['customer'],
            'receptionist'          => ['clinic_receptionist_list'],
            'clinic_admin'          => ['vendor_list'],
            'prescription'          => ['prescription'],
            'supplier'              => ['suppliers'],
            'purchased_order'       => ['purchased_order'],
            'expired_medicine'      => ['expired_medicine'],
            'referral'              => ['patient_referral'],
            'blog'                  => ['blogs'],
            'location'              => ['location', 'city', 'state', 'country'],
            'doctor_earnings'       => ['doctor_earning', 'doctor_payouts'],
            'pharma_earnings'       => ['pharma_payout'],
            'system'                => ['setting'],
        ];

        // Lab uses non-standard permission names (create_*, view_*, etc.)
        $labCrudMap = [
            'view'   => ['view_lab_tests', 'view_lab_categories', 'view_lab_results', 'view_lab_equipment', 'view_labs', 'view_lab_services', 'view_lab_orders'],
            'add'    => ['create_lab_tests', 'create_lab_categories', 'create_lab_results', 'create_lab_orders', 'create_lab_services', 'order_lab_tests'],
            'edit'   => ['edit_lab_tests', 'edit_lab_categories', 'edit_lab_results', 'edit_lab_orders', 'edit_lab_services'],
            'delete' => ['delete_lab_tests', 'delete_lab_categories', 'delete_lab_results', 'delete_lab_orders', 'delete_lab_services'],
        ];

        // Access matrix: [role => [section => [ops]]]
        // Roles: vendor (Clinic Admin), doctor, receptionist, pharma, lab_technician
        $matrix = [
            'vendor' => [
                'appointment'      => ['view', 'add', 'edit', 'delete'],
                'bed_management'   => ['view', 'add', 'edit', 'delete'],
                'encounter'        => ['view', 'add', 'edit', 'delete'],
                'doctors'          => ['view', 'add', 'edit', 'delete'],
                'doctor_session'   => ['view', 'edit'],
                'clinic'           => ['view', 'add', 'edit'],
                'service'          => ['view', 'add', 'edit', 'delete'],
                'review'           => ['view', 'delete'],
                'patient'          => ['view', 'add', 'edit'],
                'receptionist'     => ['view', 'add', 'edit', 'delete'],
                'clinic_admin'     => ['edit'],
                'prescription'     => ['view'],
                'supplier'         => ['view', 'add'],
                'purchased_order'  => ['view'],
                'expired_medicine' => ['view'],
                'referral'         => ['view', 'add', 'edit'],
                'blog'             => ['view', 'add', 'edit', 'delete'],
                'location'         => ['view'],
                'doctor_earnings'  => ['view'],
                'pharma_earnings'  => ['view'],
                'system'           => ['view', 'edit'],
                'lab'              => ['view', 'add'],
            ],
            'doctor' => [
                'appointment'      => ['view', 'add', 'edit'],
                'bed_management'   => ['view'],
                'encounter'        => ['view', 'add', 'edit'],
                'doctors'          => ['view', 'edit'],
                'doctor_session'   => ['view', 'edit'],
                'clinic'           => ['view'],
                'service'          => ['view'],
                'review'           => ['view'],
                'patient'          => ['view', 'add', 'edit'],
                'prescription'     => ['view'],
                'referral'         => ['view', 'add', 'edit'],
                'blog'             => ['view'],
                'location'         => ['view'],
                'doctor_earnings'  => ['view'],
                'system'           => ['view'],
                'lab'              => ['view', 'add'],
            ],
            'receptionist' => [
                'appointment'      => ['view', 'add', 'edit'],
                'bed_management'   => ['view'],
                'doctors'          => ['view'],
                'doctor_session'   => ['view'],
                'clinic'           => ['view'],
                'service'          => ['view'],
                'patient'          => ['view', 'add', 'edit'],
                'receptionist'     => ['edit'],
                'prescription'     => ['view'],
                'referral'         => ['view', 'add'],
                'blog'             => ['view'],
                'location'         => ['view'],
                'system'           => ['view'],
                'lab'              => ['view'],
            ],
            'pharma' => [
                'appointment'      => ['view'],
                'doctors'          => ['view'],
                'clinic'           => ['view'],
                'service'          => ['view'],
                'patient'          => ['view'],
                'prescription'     => ['view'],
                'supplier'         => ['view', 'add'],
                'purchased_order'  => ['view'],
                'expired_medicine' => ['view'],
                'referral'         => ['view', 'add'],
                'blog'             => ['view'],
                'location'         => ['view'],
                'pharma_earnings'  => ['view'],
                'system'           => ['view'],
            ],
            'lab_technician' => [
                'appointment'      => ['view'],
                'doctors'          => ['view'],
                'clinic'           => ['view'],
                'service'          => ['view'],
                'patient'          => ['view'],
                'prescription'     => ['view'],
                'referral'         => ['view', 'add'],
                'blog'             => ['view'],
                'location'         => ['view'],
                'system'           => ['view'],
                'lab'              => ['view', 'add'],
            ],
        ];

        // Build, ensure-exists, and assign per role
        foreach ($matrix as $roleName => $sections) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $permissionsForRole = [];

            foreach ($sections as $section => $ops) {
                if ($section === 'lab') {
                    foreach ($ops as $op) {
                        if (isset($labCrudMap[$op])) {
                            $permissionsForRole = array_merge($permissionsForRole, $labCrudMap[$op]);
                        }
                    }
                    continue;
                }

                if (!isset($crudSections[$section])) {
                    continue;
                }

                foreach ($crudSections[$section] as $base) {
                    foreach ($ops as $op) {
                        $permissionsForRole[] = $op . '_' . $base;
                    }
                }
            }

            // Ensure all permissions exist
            $permissionsForRole = array_unique($permissionsForRole);
            foreach ($permissionsForRole as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // Sync (replace previous permissions for these roles)
            $role->syncPermissions($permissionsForRole);
        }

        // Ensure admin & demo_admin retain full access
        $allPermissions = Permission::pluck('name')->toArray();
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($allPermissions);
        }
        if ($demoAdmin = Role::where('name', 'demo_admin')->first()) {
            $demoAdmin->givePermissionTo($allPermissions);
        }
    }
}
