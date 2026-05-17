<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdmin = Role::where('name', 'admin')->first();
        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $clinicAdmin = Role::where('name', 'clinic_admin')->first();
        $doctor = Role::where('name', 'doctor')->first();
        $receptionist = Role::where('name', 'receptionist')->first();
        $pharmacist = Role::where('name', 'pharmacist')->first();
        $labTechnologist = Role::where('name', 'lab_technician')->first();

        // Permission mapping from Excel file
        // Format: 'permission_name' => [roles that should have it]
        $permissionMap = [
            // Dashboard
            'view_total_appointments' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'view_total_active_services' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_active_clinic_admins' => [$superAdmin],
            'view_total_clinics' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_patients' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_pharmacists' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'view_total_doctors' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_medicines' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_total_revenue' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_appointment_distribution' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'view_revenue_graph' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_upcoming_appointments' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'register_clinic_admin' => [$superAdmin],
            'view_payment_history' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist],
            'view_total_labs' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],

            // Appointment
            'add_appointment' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'change_appointment_status' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'view_encounter_details' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'download_bill_pdf' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_appointment' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'export_appointment_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'filter_appointments' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],

            // Bed Management
            'allocate_patient_bed' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'add_bed' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'view_bed_type' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'view_bed_status' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],

            // Encounter
            'add_encounter' => [$superAdmin, $demoAdmin, $doctor],
            'close_checkout_encounter' => [$superAdmin, $demoAdmin, $doctor],
            'delete_encounter' => [$superAdmin],
            'manage_encounter_templates' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'view_encounter' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],

            // Problem
            'view_problem_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $pharmacist, $labTechnologist],
            'edit_problem_list' => [$superAdmin, $demoAdmin, $doctor],
            'export_problem_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],

            // Observation
            'view_observation_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_observation_list' => [$superAdmin, $demoAdmin, $doctor, $receptionist],

            // Doctors
            'add_doctor' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'edit_doctor_session' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'view_doctor_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_doctor_password' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'edit_doctor_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'delete_doctor' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Doctor Session
            'view_sessions' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'edit_sessions' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'view_doctors_session' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],

            // Specialization
            'add_specialization' => [$superAdmin],
            'change_specialization_status' => [$superAdmin],
            'delete_specialization' => [$superAdmin],
            'export_specialization_list' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Clinic
            'change_clinic_status' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'add_clinic' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'edit_clinic_session' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'add_clinic_gallery' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'view_clinic_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_clinic_profile' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'delete_clinic' => [$superAdmin],
            'filter_clinic_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_clinic_list' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Categories
            'add_categories' => [$superAdmin],
            'change_feature_status' => [$superAdmin],
            'change_active_status' => [$superAdmin],
            'edit_categories' => [$superAdmin],
            'delete_categories' => [$superAdmin],

            // Service
            'add_service' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'filter_service_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'assign_doctor_to_service' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'change_service_status' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'edit_service' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'delete_service' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'export_service_list' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Review
            'view_reviews' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'moderate_delete_reviews' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Patient
            'import_patient_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $receptionist],
            'add_patient' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'filter_patient_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_patient_status' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'view_patient_appointments' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_patient_info' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_related_patient' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'change_patient_password' => [$superAdmin, $demoAdmin, $clinicAdmin, $receptionist],
            'edit_patient_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'delete_patient' => [$superAdmin],
            'export_patient_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],

            // Receptionist
            'add_receptionist' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'change_receptionist_status' => [$superAdmin, $demoAdmin, $clinicAdmin, $receptionist],
            'change_receptionist_password' => [$superAdmin, $demoAdmin, $clinicAdmin, $receptionist],
            'edit_receptionist_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $receptionist],
            'delete_receptionist' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'export_receptionist_list' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Clinic Admin
            'add_clinic_admin' => [$superAdmin],
            'change_clinic_admin_status' => [$superAdmin],
            'change_clinic_admin_password' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'edit_clinic_admin_profile' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'filter_clinic_admin_list' => [$superAdmin],
            'delete_clinic_admin' => [$superAdmin],
            'block_unblock_clinic_admin' => [$superAdmin],
            'export_clinic_admin_list' => [$superAdmin],

            // Pharma
            'add_pharmacist' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'filter_pharmacist_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'change_pharmacist_status' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'view_pharmacist_details' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'change_pharmacist_password' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'edit_pharmacist_profile' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'delete_pharmacist' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'export_pharmacist_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],

            // Prescription
            'filter_prescription_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_prescription_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $pharmacist],

            // Supplier
            'filter_supplier_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'add_supplier' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'export_supplier_list' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],

            // Supplier Type
            'add_supplier_type' => [$superAdmin, $demoAdmin, $pharmacist],
            'export_supplier_types' => [$superAdmin, $demoAdmin, $pharmacist],

            // Purchased Order
            'view_purchase_orders' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'export_purchase_orders' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],

            // Medicine Category
            'add_medicine_category' => [$superAdmin, $demoAdmin, $pharmacist],
            'export_medicine_category' => [$superAdmin, $demoAdmin, $pharmacist],

            // Medicine Form
            'add_medicine_form' => [$superAdmin, $demoAdmin, $pharmacist],
            'export_medicine_form' => [$superAdmin, $demoAdmin, $pharmacist],

            // Expired Medicine
            'view_expired_medicines' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'export_expired_medicines' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],

            // Referral
            'add_quick_referral' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_advanced_referral' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'view_referrals' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],

            // Blog
            'add_blog' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'edit_blog' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'change_blog_status' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'view_blogs' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_blog' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // Medical Certificate
            'view_medical_certificate' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],

            // Location/Cities
            'add_location' => [$superAdmin],
            'edit_location' => [$superAdmin],
            'change_location_status' => [$superAdmin],
            'delete_location' => [$superAdmin],
            'filter_locations' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_locations' => [$superAdmin],

            // Doctor Earnings
            'view_doctor_earnings' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'payout_doctor_earnings' => [$superAdmin],

            // Pharma Earnings
            'view_pharmacist_earnings' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'payout_pharmacist_earnings' => [$superAdmin],
            'view_clinic_admin_earnings' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'payout_clinic_admin_earnings' => [$superAdmin],

            // Overviews
            'appointment_overview' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'clinic_overview' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'requested_service_overview' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_request_service' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist],
            'doctor_payout_overview' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'pharmacy_payout_overview' => [$superAdmin, $demoAdmin, $clinicAdmin, $pharmacist],
            'clinic_admin_payout_overview' => [$superAdmin, $demoAdmin, $clinicAdmin],

            // System
            'plugins' => [$superAdmin],
            'add_system_services' => [$superAdmin],
            'edit_system_services' => [$superAdmin],
            'view_system_services' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_system_service' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_system_services' => [$superAdmin],
            'export_system_services' => [$superAdmin],
            'view_incident_reports' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_incident_status' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'reply_to_incidents' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'settings' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'pages' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'app_banners' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'custom_forms' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'notifications' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'frontend_settings' => [$superAdmin],
            'database_file_backup' => [$superAdmin],
            'activity_logs' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor],
            'faq' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'access_control' => [$superAdmin],

            // Laboratory
            'add_laboratory' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'add_lab_technologist' => [$superAdmin, $demoAdmin, $clinicAdmin],
            'add_lab_test' => [$superAdmin, $demoAdmin, $clinicAdmin, $labTechnologist],
            'view_lab_orders' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],

            // Dashboard Comments (new feature based on user request)
            'view_dashboard_comments' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_dashboard_comment' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_own_dashboard_comment' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_own_dashboard_comment' => [$superAdmin, $demoAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'moderate_dashboard_comments' => [$superAdmin, $demoAdmin, $clinicAdmin],
        ];

        // Create permissions and assign to roles
        foreach ($permissionMap as $permissionName => $roles) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            foreach ($roles as $role) {
                if ($role) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        $this->command->info('Dashboard permissions seeded successfully!');
    }
}
