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
        $clinicAdmin = Role::where('name', 'clinic_admin')->first();
        $doctor = Role::where('name', 'doctor')->first();
        $receptionist = Role::where('name', 'receptionist')->first();
        $pharmacist = Role::where('name', 'pharmacist')->first();
        $labTechnologist = Role::where('name', 'lab_technician')->first();

        // Permission mapping from Excel file
        // Format: 'permission_name' => [roles that should have it]
        $permissionMap = [
            // Dashboard
            'view_total_appointments' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'view_total_active_services' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_active_clinic_admins' => [$superAdmin],
            'view_total_clinics' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_patients' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_pharmacists' => [$superAdmin, $clinicAdmin, $pharmacist],
            'view_total_doctors' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_total_medicines' => [$superAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_total_revenue' => [$superAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_appointment_distribution' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'view_revenue_graph' => [$superAdmin, $clinicAdmin, $doctor, $pharmacist],
            'view_upcoming_appointments' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'register_clinic_admin' => [$superAdmin],
            'view_payment_history' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist],
            'view_total_labs' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],

            // Appointment
            'add_appointment' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'change_appointment_status' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'view_encounter_details' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'download_bill_pdf' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_appointment' => [$superAdmin, $clinicAdmin],
            'export_appointment_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'filter_appointments' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],

            // Bed Management
            'allocate_patient_bed' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'add_bed' => [$superAdmin, $clinicAdmin],
            'view_bed_type' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'view_bed_status' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],

            // Encounter
            'add_encounter' => [$superAdmin, $doctor],
            'close_checkout_encounter' => [$superAdmin, $doctor],
            'delete_encounter' => [$superAdmin],
            'manage_encounter_templates' => [$superAdmin, $clinicAdmin, $doctor],
            'view_encounter' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],

            // Problem
            'view_problem_list' => [$superAdmin, $clinicAdmin, $doctor, $pharmacist, $labTechnologist],
            'edit_problem_list' => [$superAdmin, $doctor],
            'export_problem_list' => [$superAdmin, $clinicAdmin, $doctor],

            // Observation
            'view_observation_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_observation_list' => [$superAdmin, $doctor, $receptionist],

            // Doctors
            'add_doctor' => [$superAdmin, $clinicAdmin],
            'edit_doctor_session' => [$superAdmin, $clinicAdmin, $doctor],
            'view_doctor_profile' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_doctor_password' => [$superAdmin, $clinicAdmin, $doctor],
            'edit_doctor_profile' => [$superAdmin, $clinicAdmin, $doctor],
            'delete_doctor' => [$superAdmin, $clinicAdmin],

            // Doctor Session
            'view_sessions' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'edit_sessions' => [$superAdmin, $clinicAdmin, $doctor],
            'view_doctors_session' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],

            // Specialization
            'add_specialization' => [$superAdmin],
            'change_specialization_status' => [$superAdmin],
            'delete_specialization' => [$superAdmin],
            'export_specialization_list' => [$superAdmin, $clinicAdmin],

            // Clinic
            'change_clinic_status' => [$superAdmin, $clinicAdmin],
            'add_clinic' => [$superAdmin, $clinicAdmin],
            'edit_clinic_session' => [$superAdmin, $clinicAdmin],
            'add_clinic_gallery' => [$superAdmin, $clinicAdmin],
            'view_clinic_profile' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_clinic_profile' => [$superAdmin, $clinicAdmin],
            'delete_clinic' => [$superAdmin],
            'filter_clinic_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_clinic_list' => [$superAdmin, $clinicAdmin],

            // Categories
            'add_categories' => [$superAdmin],
            'change_feature_status' => [$superAdmin],
            'change_active_status' => [$superAdmin],
            'edit_categories' => [$superAdmin],
            'delete_categories' => [$superAdmin],

            // Service
            'add_service' => [$superAdmin, $clinicAdmin],
            'filter_service_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'assign_doctor_to_service' => [$superAdmin, $clinicAdmin],
            'change_service_status' => [$superAdmin, $clinicAdmin],
            'edit_service' => [$superAdmin, $clinicAdmin],
            'delete_service' => [$superAdmin, $clinicAdmin],
            'export_service_list' => [$superAdmin, $clinicAdmin],

            // Review
            'view_reviews' => [$superAdmin, $clinicAdmin, $doctor],
            'moderate_delete_reviews' => [$superAdmin, $clinicAdmin],

            // Patient
            'import_patient_list' => [$superAdmin, $clinicAdmin, $receptionist],
            'add_patient' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'filter_patient_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_patient_status' => [$superAdmin, $clinicAdmin],
            'view_patient_appointments' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_patient_info' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_related_patient' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'change_patient_password' => [$superAdmin, $clinicAdmin, $receptionist],
            'edit_patient_profile' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'delete_patient' => [$superAdmin],
            'export_patient_list' => [$superAdmin, $clinicAdmin, $doctor],

            // Receptionist
            'add_receptionist' => [$superAdmin, $clinicAdmin],
            'change_receptionist_status' => [$superAdmin, $clinicAdmin, $receptionist],
            'change_receptionist_password' => [$superAdmin, $clinicAdmin, $receptionist],
            'edit_receptionist_profile' => [$superAdmin, $clinicAdmin, $receptionist],
            'delete_receptionist' => [$superAdmin, $clinicAdmin],
            'export_receptionist_list' => [$superAdmin, $clinicAdmin],

            // Clinic Admin
            'add_clinic_admin' => [$superAdmin],
            'change_clinic_admin_status' => [$superAdmin],
            'change_clinic_admin_password' => [$superAdmin, $clinicAdmin],
            'edit_clinic_admin_profile' => [$superAdmin, $clinicAdmin],
            'filter_clinic_admin_list' => [$superAdmin],
            'delete_clinic_admin' => [$superAdmin],
            'block_unblock_clinic_admin' => [$superAdmin],
            'export_clinic_admin_list' => [$superAdmin],

            // Pharma
            'add_pharmacist' => [$superAdmin, $clinicAdmin],
            'filter_pharmacist_list' => [$superAdmin, $clinicAdmin, $pharmacist],
            'change_pharmacist_status' => [$superAdmin, $clinicAdmin, $pharmacist],
            'view_pharmacist_details' => [$superAdmin, $clinicAdmin, $pharmacist],
            'change_pharmacist_password' => [$superAdmin, $clinicAdmin, $pharmacist],
            'edit_pharmacist_profile' => [$superAdmin, $clinicAdmin, $pharmacist],
            'delete_pharmacist' => [$superAdmin, $clinicAdmin],
            'export_pharmacist_list' => [$superAdmin, $clinicAdmin, $pharmacist],

            // Prescription
            'filter_prescription_list' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_prescription_list' => [$superAdmin, $clinicAdmin, $doctor, $pharmacist],

            // Supplier
            'filter_supplier_list' => [$superAdmin, $clinicAdmin, $pharmacist],
            'add_supplier' => [$superAdmin, $clinicAdmin, $pharmacist],
            'export_supplier_list' => [$superAdmin, $clinicAdmin, $pharmacist],

            // Supplier Type
            'add_supplier_type' => [$superAdmin, $pharmacist],
            'export_supplier_types' => [$superAdmin, $pharmacist],

            // Purchased Order
            'view_purchase_orders' => [$superAdmin, $clinicAdmin, $pharmacist],
            'export_purchase_orders' => [$superAdmin, $clinicAdmin, $pharmacist],

            // Medicine Category
            'add_medicine_category' => [$superAdmin, $pharmacist],
            'export_medicine_category' => [$superAdmin, $pharmacist],

            // Medicine Form
            'add_medicine_form' => [$superAdmin, $pharmacist],
            'export_medicine_form' => [$superAdmin, $pharmacist],

            // Expired Medicine
            'view_expired_medicines' => [$superAdmin, $clinicAdmin, $pharmacist],
            'export_expired_medicines' => [$superAdmin, $clinicAdmin, $pharmacist],

            // Referral
            'add_quick_referral' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_advanced_referral' => [$superAdmin, $clinicAdmin, $doctor],
            'view_referrals' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],

            // Blog
            'add_blog' => [$superAdmin, $clinicAdmin],
            'edit_blog' => [$superAdmin, $clinicAdmin],
            'change_blog_status' => [$superAdmin, $clinicAdmin],
            'view_blogs' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_blog' => [$superAdmin, $clinicAdmin],

            // Medical Certificate
            'view_medical_certificate' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],

            // Location/Cities
            'add_location' => [$superAdmin],
            'edit_location' => [$superAdmin],
            'change_location_status' => [$superAdmin],
            'delete_location' => [$superAdmin],
            'filter_locations' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'export_locations' => [$superAdmin],

            // Doctor Earnings
            'view_doctor_earnings' => [$superAdmin, $clinicAdmin, $doctor],
            'payout_doctor_earnings' => [$superAdmin],

            // Pharma Earnings
            'view_pharmacist_earnings' => [$superAdmin, $clinicAdmin, $pharmacist],
            'payout_pharmacist_earnings' => [$superAdmin],
            'view_clinic_admin_earnings' => [$superAdmin, $clinicAdmin],
            'payout_clinic_admin_earnings' => [$superAdmin],

            // Overviews
            'appointment_overview' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],
            'clinic_overview' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'requested_service_overview' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_request_service' => [$superAdmin, $clinicAdmin, $doctor, $receptionist],
            'doctor_payout_overview' => [$superAdmin, $clinicAdmin, $doctor],
            'pharmacy_payout_overview' => [$superAdmin, $clinicAdmin, $pharmacist],
            'clinic_admin_payout_overview' => [$superAdmin, $clinicAdmin],

            // System
            'plugins' => [$superAdmin],
            'add_system_services' => [$superAdmin],
            'edit_system_services' => [$superAdmin],
            'view_system_services' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'view_system_service' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_system_services' => [$superAdmin],
            'export_system_services' => [$superAdmin],
            'view_incident_reports' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'change_incident_status' => [$superAdmin, $clinicAdmin],
            'reply_to_incidents' => [$superAdmin, $clinicAdmin],
            'settings' => [$superAdmin, $clinicAdmin],
            'pages' => [$superAdmin, $clinicAdmin],
            'app_banners' => [$superAdmin, $clinicAdmin],
            'custom_forms' => [$superAdmin, $clinicAdmin],
            'notifications' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'frontend_settings' => [$superAdmin],
            'database_file_backup' => [$superAdmin],
            'activity_logs' => [$superAdmin, $clinicAdmin, $doctor],
            'faq' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'access_control' => [$superAdmin],

            // Laboratory
            'add_laboratory' => [$superAdmin, $clinicAdmin],
            'add_lab_technologist' => [$superAdmin, $clinicAdmin],
            'add_lab_test' => [$superAdmin, $clinicAdmin, $labTechnologist],
            'view_lab_orders' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $labTechnologist],

            // Dashboard Comments (new feature based on user request)
            'view_dashboard_comments' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'add_dashboard_comment' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'edit_own_dashboard_comment' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'delete_own_dashboard_comment' => [$superAdmin, $clinicAdmin, $doctor, $receptionist, $pharmacist, $labTechnologist],
            'moderate_dashboard_comments' => [$superAdmin, $clinicAdmin],
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
