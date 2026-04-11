<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * MobileApiPermissionSeeder
 *
 * This seeder ensures all permissions required by the mobile app API routes
 * exist in the database and are correctly assigned to each role.
 *
 * The mobile app API routes use simplified permission names (e.g. 'view_appointment')
 * which differ from the web permission names (e.g. 'view_clinic_appointment_list').
 * The ApiPermissionMiddleware handles the name mapping at runtime, but this seeder
 * also creates the missing permissions so the system is fully consistent.
 *
 * Run with: php artisan db:seed --class=MobileApiPermissionSeeder
 */
class MobileApiPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        $admin        = Role::firstOrCreate(['name' => 'admin'],        ['title' => 'Admin', 'is_fixed' => true]);
        $demo_admin   = Role::firstOrCreate(['name' => 'demo_admin'],   ['title' => 'Demo Admin', 'is_fixed' => true]);
        $vendor       = Role::firstOrCreate(['name' => 'vendor'],       ['title' => 'Clinic Admin', 'is_fixed' => true]);
        $doctor       = Role::firstOrCreate(['name' => 'doctor'],       ['title' => 'Doctor', 'is_fixed' => true]);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist'], ['title' => 'Receptionist', 'is_fixed' => true]);
        $user         = Role::firstOrCreate(['name' => 'user'],         ['title' => 'Patient', 'is_fixed' => true]);
        $pharma       = Role::firstOrCreate(['name' => 'pharma'],       ['title' => 'Pharma', 'is_fixed' => true]);
        $lab_tech     = Role::firstOrCreate(['name' => 'lab_technician'], ['title' => 'Lab Technician', 'is_fixed' => true]);

        // ─────────────────────────────────────────────────────────────
        // 1. Ensure ALL permissions that the API routes reference exist
        //    These are the permission names used in api.permission:xxx
        // ─────────────────────────────────────────────────────────────
        $apiPermissions = [
            // Dashboard
            'view_dashboard',

            // Appointments
            'view_appointment', 'add_appointment', 'edit_appointment', 'delete_appointment',

            // Encounters
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',

            // Medical Reports
            'view_medical_report', 'add_medical_report', 'edit_medical_report', 'delete_medical_report',

            // Prescriptions
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',

            // Billing
            'view_billing', 'add_billing', 'edit_billing', 'delete_billing',

            // Categories
            'view_categories', 'add_categories', 'edit_categories', 'delete_categories',

            // Services
            'view_services', 'add_services', 'edit_services', 'delete_services',

            // Clinics
            'view_clinics', 'add_clinics', 'edit_clinics', 'delete_clinics',

            // Doctors
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',

            // Patients
            'view_patients', 'add_patients', 'edit_patients', 'delete_patients',

            // Receptionists
            'view_receptionists', 'add_receptionists', 'edit_receptionists', 'delete_receptionists',

            // Backups
            'view_backups',
        ];

        foreach ($apiPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName], ['is_fixed' => false]);
        }

        // ─────────────────────────────────────────────────────────────
        // 2. Also ensure the WEB permission equivalents exist
        //    (these may already exist from PermissionRoleTableSeeder)
        // ─────────────────────────────────────────────────────────────
        $webPermissions = [
            'view_clinic_appointment_list', 'add_clinic_appointment_list',
            'edit_clinic_appointment_list', 'delete_clinic_appointment_list',
            'view_clinics_center', 'add_clinics_center', 'edit_clinics_center', 'delete_clinics_center',
            'view_clinics_service', 'add_clinics_service', 'edit_clinics_service', 'delete_clinics_service',
            'view_clinics_category', 'add_clinics_category', 'edit_clinics_category', 'delete_clinics_category',
            'view_customer', 'add_customer', 'edit_customer', 'delete_customer',
            'view_clinic_receptionist_list', 'add_clinic_receptionist_list',
            'edit_clinic_receptionist_list', 'delete_clinic_receptionist_list',
            'view_billing_record', 'add_billing_record', 'edit_billing_record', 'delete_billing_record',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_doctors_session', 'add_doctors_session', 'edit_doctors_session', 'delete_doctors_session',
            'view_setting', 'add_setting', 'edit_setting', 'delete_setting',
            'view_notification',
            'view_reviews',
            'view_doctor_earning',
            'view_doctor_payouts', 'add_doctor_payouts', 'edit_doctor_payouts', 'delete_doctor_payouts',
            'view_vendor_payouts', 'add_vendor_payouts', 'edit_vendor_payouts', 'delete_vendor_payouts',
            'view_backup',
            'setting_doctor_holiday', 'setting_holiday', 'setting_telemed_service', 'setting_quick_booking',
            'view_request_service', 'add_request_service', 'edit_request_service', 'delete_request_service',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_medicine', 'add_medicine', 'edit_medicine', 'delete_medicine',
            'view_expired_medicine',
            'view_suppliers', 'add_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_purchased_order', 'add_purchased_order', 'edit_purchased_order', 'delete_purchased_order',
            'view_tax', 'add_tax', 'edit_tax', 'delete_tax',
            'view_pharma_billing_record',
            'view_pharma_payout', 'add_pharma_payout', 'edit_pharma_payout', 'delete_pharma_payout',
            'view_bed_master', 'add_bed_master', 'edit_bed_master', 'delete_bed_master',
            'view_allocations', 'add_allocations', 'edit_allocations', 'delete_allocations',
            'view_incidence_report', 'add_incidence_report', 'edit_incidence_report', 'delete_incidence_report',
        ];

        foreach ($webPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName], ['is_fixed' => true]);
        }

        // ─────────────────────────────────────────────────────────────
        // 3. Assign API + Web permissions to each role
        // ─────────────────────────────────────────────────────────────

        // --- VENDOR (Clinic Admin) ---
        $vendorPerms = [
            // API permissions
            'view_dashboard',
            'view_appointment', 'add_appointment', 'edit_appointment', 'delete_appointment',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_medical_report', 'add_medical_report', 'edit_medical_report', 'delete_medical_report',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing', 'add_billing', 'edit_billing', 'delete_billing',
            'view_categories', 'add_categories', 'edit_categories', 'delete_categories',
            'view_services', 'add_services', 'edit_services', 'delete_services',
            'view_clinics', 'add_clinics', 'edit_clinics', 'delete_clinics',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_patients', 'add_patients', 'edit_patients', 'delete_patients',
            'view_receptionists', 'add_receptionists', 'edit_receptionists', 'delete_receptionists',
            // Web permissions (for completeness / backward compatibility)
            'view_clinics_center', 'add_clinics_center', 'edit_clinics_center', 'delete_clinics_center',
            'view_clinics_service', 'add_clinics_service', 'edit_clinics_service', 'delete_clinics_service',
            'view_clinics_category', 'add_clinics_category', 'edit_clinics_category', 'delete_clinics_category',
            'view_clinic_appointment_list', 'add_clinic_appointment_list', 'delete_clinic_appointment_list',
            'view_customer', 'add_customer', 'edit_customer', 'delete_customer',
            'view_clinic_receptionist_list', 'add_clinic_receptionist_list',
            'edit_clinic_receptionist_list', 'delete_clinic_receptionist_list',
            'view_billing_record', 'add_billing_record', 'edit_billing_record', 'delete_billing_record',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_doctors_session', 'add_doctors_session', 'edit_doctors_session', 'delete_doctors_session',
            'view_setting', 'add_setting', 'edit_setting', 'delete_setting',
            'setting_quick_booking', 'setting_holiday', 'setting_telemed_service',
            'view_request_service', 'add_request_service', 'edit_request_service', 'delete_request_service',
            'view_doctor_earning',
            'view_doctor_payouts', 'add_doctor_payouts', 'edit_doctor_payouts', 'delete_doctor_payouts',
            'view_vendor_payouts', 'add_vendor_payouts', 'edit_vendor_payouts', 'delete_vendor_payouts',
            'view_notification',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_medicine', 'add_medicine', 'edit_medicine', 'delete_medicine',
            'view_pharma_payout', 'add_pharma_payout', 'edit_pharma_payout', 'delete_pharma_payout',
            'view_expired_medicine',
            'view_suppliers', 'add_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_purchased_order', 'add_purchased_order', 'edit_purchased_order', 'delete_purchased_order',
            'view_tax', 'add_tax', 'edit_tax', 'delete_tax',
            'view_pharma_billing_record',
            'view_bed_master', 'add_bed_master', 'edit_bed_master', 'delete_bed_master',
            'view_allocations', 'add_allocations', 'edit_allocations', 'delete_allocations',
        ];
        $this->safeAssignPermissions($vendor, $vendorPerms);

        // --- DOCTOR ---
        $doctorPerms = [
            // API permissions
            'view_dashboard',
            'view_appointment', 'add_appointment', 'edit_appointment',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_medical_report', 'add_medical_report', 'edit_medical_report', 'delete_medical_report',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing', 'add_billing', 'edit_billing', 'delete_billing',
            'view_services',
            'view_clinics',
            'view_doctors',
            'view_patients', 'add_patients',
            // Web permissions
            'view_clinics_center',
            'view_clinics_service',
            'view_clinic_appointment_list', 'add_clinic_appointment_list',
            'view_customer', 'add_customer',
            'view_setting', 'add_setting', 'edit_setting', 'delete_setting',
            'setting_doctor_holiday',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_billing_record', 'add_billing_record', 'edit_billing_record', 'delete_billing_record',
            'view_doctors_session', 'edit_doctors_session',
            'view_reviews',
            'view_notification',
            'view_doctor_payouts',
            'view_bed_master', 'add_bed_master', 'edit_bed_master', 'delete_bed_master',
            'view_allocations', 'add_allocations', 'edit_allocations', 'delete_allocations',
            'view_incidence_report', 'add_incidence_report', 'edit_incidence_report',
        ];
        $this->safeAssignPermissions($doctor, $doctorPerms);

        // --- RECEPTIONIST ---
        $receptionistPerms = [
            // API permissions
            'view_dashboard',
            'view_appointment', 'add_appointment', 'delete_appointment',
            'view_encounter',
            'view_billing',
            'view_categories',
            'view_services', 'add_services', 'edit_services', 'delete_services',
            'view_clinics',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_patients', 'add_patients', 'edit_patients', 'delete_patients',
            'view_receptionists',
            // Web permissions
            'view_clinics_center', 'edit_clinics_center',
            'view_clinics_service', 'add_clinics_service', 'edit_clinics_service', 'delete_clinics_service',
            'view_clinic_appointment_list', 'add_clinic_appointment_list', 'delete_clinic_appointment_list',
            'view_customer', 'add_customer', 'edit_customer', 'delete_customer',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_doctors_session', 'add_doctors_session', 'edit_doctors_session', 'delete_doctors_session',
            'view_encounter',
            'view_billing_record',
            'view_clinic_receptionist_list',
            'view_bed_master', 'add_bed_master', 'edit_bed_master', 'delete_bed_master',
            'view_allocations', 'add_allocations', 'edit_allocations', 'delete_allocations',
        ];
        $this->safeAssignPermissions($receptionist, $receptionistPerms);

        // --- PATIENT (user) ---
        $patientPerms = [
            // API permissions
            'view_dashboard',
            'view_appointment', 'add_appointment',
            'view_services',
            'view_clinics',
            'view_doctors',
            'view_billing',
            'view_encounter',
            'view_prescription',
            // Web permissions
            'view_clinic_appointment_list',
            'view_clinics_center',
            'view_clinics_service',
            'view_customer',
        ];
        $this->safeAssignPermissions($user, $patientPerms);

        // --- PHARMA ---
        $pharmaPerms = [
            // API permissions
            'view_dashboard',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing',
            // Web permissions (already assigned by PermissionRoleTableSeeder, adding for safety)
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_medicine', 'add_medicine', 'edit_medicine', 'delete_medicine',
            'view_pharma_payout', 'add_pharma_payout', 'edit_pharma_payout', 'delete_pharma_payout',
            'view_expired_medicine',
            'view_suppliers', 'add_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_purchased_order', 'add_purchased_order', 'edit_purchased_order', 'delete_purchased_order',
            'view_tax', 'add_tax', 'edit_tax', 'delete_tax',
            'view_setting',
            'view_notification',
            'view_pharma_billing_record',
        ];
        $this->safeAssignPermissions($pharma, $pharmaPerms);

        // --- LAB TECHNICIAN ---
        $labTechPerms = [
            // API permissions
            'view_dashboard',
            'view_patients',
            'view_encounter',
            // Web permissions
            'view_customer',
        ];
        $this->safeAssignPermissions($lab_tech, $labTechPerms);

        // --- ADMIN gets ALL permissions ---
        $admin->givePermissionTo(Permission::all());

        // --- DEMO ADMIN gets all except payment/sensitive settings ---
        $excludeForDemo = ['setting_payment_method', 'setting_other_setting'];
        $demoPerms = Permission::whereNotIn('name', $excludeForDemo)->get();
        $demo_admin->syncPermissions($demoPerms);

        Log::info('MobileApiPermissionSeeder: All API permissions seeded successfully.');
    }

    /**
     * Safely assign permissions to a role, skipping any that don't exist in DB.
     */
    private function safeAssignPermissions(Role $role, array $permissionNames): void
    {
        $validPerms = [];
        foreach (array_unique($permissionNames) as $permName) {
            try {
                $perm = Permission::where('name', $permName)->first();
                if ($perm) {
                    $validPerms[] = $permName;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!empty($validPerms)) {
            // Use givePermissionTo to ADD permissions without removing existing ones
            foreach ($validPerms as $permName) {
                if (!$role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permName);
                }
            }
        }
    }
}
