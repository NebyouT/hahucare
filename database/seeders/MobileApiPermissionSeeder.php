<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * MobileApiPermissionSeeder
 *
 * Creates a DEDICATED set of mobile app permissions, completely separate from
 * the admin panel (web) permissions. All mobile permissions are prefixed with
 * "mobile_" so they never conflict with the web permissions.
 *
 * Admin panel:  view_clinics_category, add_clinics_category, ...
 * Mobile app:   mobile_view_categories, mobile_add_categories, ...
 *
 * The ApiPermissionMiddleware auto-prefixes "mobile_" at runtime, so the
 * route files stay clean (api.permission:view_categories).
 *
 * Run with: php artisan db:seed --class=MobileApiPermissionSeeder
 */
class MobileApiPermissionSeeder extends Seeder
{
    /**
     * All mobile permission names extracted from every API route file.
     * These are the EXACT names stored in the permissions table.
     *
     * Source route files:
     *   routes/api.php
     *   Modules/Appointment/routes/api.php
     *   Modules/Clinic/Routes/api.php
     */
    private array $allMobilePermissions = [
        // ─── Dashboard ───
        'mobile_view_dashboard',

        // ─── Categories ───
        'mobile_view_categories',
        'mobile_add_categories',
        'mobile_edit_categories',
        'mobile_delete_categories',

        // ─── Services ───
        'mobile_view_services',
        'mobile_add_services',
        'mobile_edit_services',
        'mobile_delete_services',

        // ─── Clinics ───
        'mobile_view_clinics',
        'mobile_add_clinics',
        'mobile_edit_clinics',
        'mobile_delete_clinics',

        // ─── Doctors ───
        'mobile_view_doctors',
        'mobile_add_doctors',
        'mobile_edit_doctors',
        'mobile_delete_doctors',

        // ─── Patients ───
        'mobile_view_patients',
        'mobile_add_patients',
        'mobile_edit_patients',
        'mobile_delete_patients',

        // ─── Receptionists ───
        'mobile_view_receptionists',
        'mobile_add_receptionists',
        'mobile_edit_receptionists',
        'mobile_delete_receptionists',

        // ─── Appointments ───
        'mobile_view_appointment',
        'mobile_add_appointment',
        'mobile_edit_appointment',
        'mobile_delete_appointment',

        // ─── Encounters ───
        'mobile_view_encounter',
        'mobile_add_encounter',
        'mobile_edit_encounter',
        'mobile_delete_encounter',

        // ─── Medical Reports ───
        'mobile_view_medical_report',
        'mobile_add_medical_report',
        'mobile_edit_medical_report',
        'mobile_delete_medical_report',

        // ─── Prescriptions ───
        'mobile_view_prescription',
        'mobile_add_prescription',
        'mobile_edit_prescription',
        'mobile_delete_prescription',

        // ─── Billing ───
        'mobile_view_billing',
        'mobile_add_billing',
        'mobile_edit_billing',
        'mobile_delete_billing',

        // ─── Backups ───
        'mobile_view_backups',
    ];

    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. Create all mobile permissions in the database
        // ─────────────────────────────────────────────────────────────
        foreach ($this->allMobilePermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['is_fixed' => false]
            );
        }

        $this->command->info('Created ' . count($this->allMobilePermissions) . ' mobile permissions.');

        // ─────────────────────────────────────────────────────────────
        // 2. Ensure all roles exist
        // ─────────────────────────────────────────────────────────────
        $vendor       = Role::firstOrCreate(['name' => 'vendor'],         ['title' => 'Clinic Admin', 'is_fixed' => true]);
        $doctor       = Role::firstOrCreate(['name' => 'doctor'],         ['title' => 'Doctor', 'is_fixed' => true]);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist'],   ['title' => 'Receptionist', 'is_fixed' => true]);
        $user         = Role::firstOrCreate(['name' => 'user'],           ['title' => 'Patient', 'is_fixed' => true]);
        $pharma       = Role::firstOrCreate(['name' => 'pharma'],         ['title' => 'Pharma', 'is_fixed' => true]);
        $lab_tech     = Role::firstOrCreate(['name' => 'lab_technician'], ['title' => 'Lab Technician', 'is_fixed' => true]);

        // ─────────────────────────────────────────────────────────────
        // 3. Assign mobile permissions to each role
        //    Each role gets ONLY the mobile features they should access.
        //    Admin/demo_admin are bypassed in middleware, no need to assign.
        // ─────────────────────────────────────────────────────────────

        // ─── VENDOR (Clinic Admin) — Full mobile access ───
        $this->assignMobilePerms($vendor, [
            'mobile_view_dashboard',
            // Appointments - full CRUD
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment', 'mobile_delete_appointment',
            // Encounters - full CRUD
            'mobile_view_encounter', 'mobile_add_encounter', 'mobile_edit_encounter', 'mobile_delete_encounter',
            // Medical Reports - full CRUD
            'mobile_view_medical_report', 'mobile_add_medical_report', 'mobile_edit_medical_report', 'mobile_delete_medical_report',
            // Prescriptions - full CRUD
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            // Billing - full CRUD
            'mobile_view_billing', 'mobile_add_billing', 'mobile_edit_billing', 'mobile_delete_billing',
            // Categories - full CRUD
            'mobile_view_categories', 'mobile_add_categories', 'mobile_edit_categories', 'mobile_delete_categories',
            // Services - full CRUD
            'mobile_view_services', 'mobile_add_services', 'mobile_edit_services', 'mobile_delete_services',
            // Clinics - full CRUD
            'mobile_view_clinics', 'mobile_add_clinics', 'mobile_edit_clinics', 'mobile_delete_clinics',
            // Doctors - full CRUD
            'mobile_view_doctors', 'mobile_add_doctors', 'mobile_edit_doctors', 'mobile_delete_doctors',
            // Patients - full CRUD
            'mobile_view_patients', 'mobile_add_patients', 'mobile_edit_patients', 'mobile_delete_patients',
            // Receptionists - full CRUD
            'mobile_view_receptionists', 'mobile_add_receptionists', 'mobile_edit_receptionists', 'mobile_delete_receptionists',
            // Backups
            'mobile_view_backups',
        ]);

        // ─── DOCTOR — Clinical focus ───
        $this->assignMobilePerms($doctor, [
            'mobile_view_dashboard',
            // Appointments - view, add, edit (no delete)
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment',
            // Encounters - full CRUD
            'mobile_view_encounter', 'mobile_add_encounter', 'mobile_edit_encounter', 'mobile_delete_encounter',
            // Medical Reports - full CRUD
            'mobile_view_medical_report', 'mobile_add_medical_report', 'mobile_edit_medical_report', 'mobile_delete_medical_report',
            // Prescriptions - full CRUD
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            // Billing - full CRUD
            'mobile_view_billing', 'mobile_add_billing', 'mobile_edit_billing', 'mobile_delete_billing',
            // View-only access
            'mobile_view_categories',
            'mobile_view_services',
            'mobile_view_clinics',
            'mobile_view_doctors',
            // Patients - view + add
            'mobile_view_patients', 'mobile_add_patients',
        ]);

        // ─── RECEPTIONIST — Operational focus ───
        $this->assignMobilePerms($receptionist, [
            'mobile_view_dashboard',
            // Appointments - view, add, edit, delete
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment', 'mobile_delete_appointment',
            // Encounters - view only
            'mobile_view_encounter',
            // Billing - view + add
            'mobile_view_billing', 'mobile_add_billing',
            // Categories - view only
            'mobile_view_categories',
            // Services - full CRUD
            'mobile_view_services', 'mobile_add_services', 'mobile_edit_services', 'mobile_delete_services',
            // Clinics - view only
            'mobile_view_clinics',
            // Doctors - full CRUD
            'mobile_view_doctors', 'mobile_add_doctors', 'mobile_edit_doctors', 'mobile_delete_doctors',
            // Patients - full CRUD
            'mobile_view_patients', 'mobile_add_patients', 'mobile_edit_patients', 'mobile_delete_patients',
            // Receptionists - view only
            'mobile_view_receptionists',
        ]);

        // ─── PATIENT (user) — Limited consumer access ───
        $this->assignMobilePerms($user, [
            'mobile_view_dashboard',
            // Appointments - view + book
            'mobile_view_appointment', 'mobile_add_appointment',
            // View-only access to browse
            'mobile_view_categories',
            'mobile_view_services',
            'mobile_view_clinics',
            'mobile_view_doctors',
            // Billing - view own bills
            'mobile_view_billing',
            // Encounter - view own records
            'mobile_view_encounter',
            // Prescription - view own prescriptions
            'mobile_view_prescription',
        ]);

        // ─── PHARMA — Pharmacy focus ───
        $this->assignMobilePerms($pharma, [
            'mobile_view_dashboard',
            // Prescriptions - full CRUD
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            // Billing - view
            'mobile_view_billing',
        ]);

        // ─── LAB TECHNICIAN — Lab focus ───
        $this->assignMobilePerms($lab_tech, [
            'mobile_view_dashboard',
            // Patients - view for lab work
            'mobile_view_patients',
            // Encounters - view for lab orders
            'mobile_view_encounter',
        ]);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Mobile permissions assigned to all roles successfully.');
        Log::info('MobileApiPermissionSeeder: All mobile permissions seeded and assigned.');
    }

    /**
     * Assign a list of mobile permissions to a role.
     * Uses givePermissionTo to ADD without removing existing permissions.
     */
    private function assignMobilePerms(Role $role, array $permissionNames): void
    {
        $count = 0;
        foreach ($permissionNames as $permName) {
            if (!$role->hasPermissionTo($permName)) {
                $role->givePermissionTo($permName);
                $count++;
            }
        }
        $this->command->line("  {$role->name}: {$count} new mobile permissions assigned.");
    }
}
