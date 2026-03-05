# API Permission Mapping

This document maps API endpoints to their required permissions based on the existing Spatie permission system.

## Permission Structure

Permissions follow the pattern: `{action}_{resource}`
- Actions: `view`, `create`, `edit`, `delete`, `add`, `approve`, `print`, `export`
- Resources: Based on module entities (e.g., `appointments`, `clinics`, `doctors`, `labs`, etc.)

## Core API Routes (`routes/api.php`)

### Authentication Routes (No permissions required)
- `POST /api/register` - Public
- `POST /api/login` - Public
- `POST /api/social-login` - Public
- `POST /api/forgot-password` - Public
- `POST /api/otp-verify` - Public
- `GET /api/logout` - Authenticated only

### Protected Routes (Require auth:sanctum)
- `GET /api/backup` - Permission: `view_backups`
- `POST /api/download-backup-files` - Permission: `view_backups`
- `POST /api/update-profile` - Authenticated only (own profile)
- `POST /api/change-password` - Authenticated only (own password)
- `POST /api/delete-account` - Authenticated only (own account)

### Dashboard Routes
- `GET /api/vendor-dashboard-list` - Permission: `view_dashboard` (vendor role)
- `GET /api/doctor-dashboard-list` - Permission: `view_dashboard` (doctor role)
- `GET /api/receptionist-dashboard-list` - Permission: `view_dashboard` (receptionist role)
- `GET /api/pharma-dashboard-list` - Permission: `view_dashboard` (pharma role)

## Appointment Module (`Modules/Appointment/routes/api.php`)

### Appointments
- `POST /api/save-booking` - Permission: `add_appointment`
- `POST /api/update-booking/{id}` - Permission: `edit_appointment`
- `GET /api/appointment-list` - Permission: `view_appointment`
- `GET /api/appointment-detail` - Permission: `view_appointment`
- `POST /api/save-payment` - Permission: `edit_appointment`
- `POST /api/update-status/{id}` - Permission: `edit_appointment`
- `POST /api/reschedule-booking` - Permission: `edit_appointment`
- `POST /api/cancel-appointment/{id}` - Permission: `delete_appointment`

### Patient Encounters
- `GET /api/encounter-list` - Permission: `view_encounter`
- `POST /api/save-encounter` - Permission: `add_encounter`
- `POST /api/update-encounter/{id}` - Permission: `edit_encounter`
- `POST /api/delete-encounter/{id}` - Permission: `delete_encounter`
- `GET /api/encounter-details` - Permission: `view_encounter`
- `GET /api/download-encounter-invoice` - Permission: `view_encounter`
- `GET /api/download-prescription` - Permission: `view_encounter`

### Medical Reports
- `GET /api/get-medical-report` - Permission: `view_medical_report`
- `POST /api/save-medical-report` - Permission: `add_medical_report`
- `POST /api/update-medical-report/{id}` - Permission: `edit_medical_report`
- `GET /api/delete-medical-report/{id}` - Permission: `delete_medical_report`

### Prescriptions
- `GET /api/get-prescription` - Permission: `view_prescription`
- `POST /api/save-prescription` - Permission: `add_prescription`
- `POST /api/update-prescription/{id}` - Permission: `edit_prescription`
- `GET /api/delete-prescription/{id}` - Permission: `delete_prescription`

### Billing
- `POST /api/save-billing-details` - Permission: `add_billing`
- `GET /api/billing-list` - Permission: `view_billing`
- `POST /api/save-billing-items` - Permission: `add_billing`
- `GET /api/billing-item-list` - Permission: `view_billing`

## Clinic Module (`Modules/Clinic/Routes/api.php`)

### Public Routes (No auth required)
- `GET /api/get-service-list` - Public
- `GET /api/get-doctor-list` - Public
- `GET /api/get-category-list` - Public
- `GET /api/get-clinic-list` - Public
- `GET /api/get-time-slots` - Public

### Categories
- `GET /api/get-categories` - Permission: `view_categories`
- `POST /api/save-clinic-category` - Permission: `add_categories`
- `POST /api/update-clinic-category/{id}` - Permission: `edit_categories`
- `POST /api/delete-clinic-category/{id}` - Permission: `delete_categories`

### Services
- `GET /api/get-services` - Permission: `view_services`
- `POST /api/save-clinic-service` - Permission: `add_services`
- `POST /api/update-clinic-service/{id}` - Permission: `edit_services`
- `POST /api/delete-clinic-service/{id}` - Permission: `delete_services`

### Clinics
- `GET /api/get-clinics` - Permission: `view_clinics`
- `POST /api/save-clinic` - Permission: `add_clinics`
- `POST /api/update-clinic/{id}` - Permission: `edit_clinics`
- `POST /api/delete-clinic/{id}` - Permission: `delete_clinics`
- `POST /api/save-clinic-gallery` - Permission: `edit_clinics`
- `GET /api/clinic-session` - Permission: `view_clinics`

### Doctors
- `GET /api/get-doctors` - Permission: `view_doctors`
- `POST /api/save-doctor` - Permission: `add_doctors`
- `POST /api/update-doctor/{id}` - Permission: `edit_doctors`
- `POST /api/delete-doctor/{id}` - Permission: `delete_doctors`
- `GET /api/get-doctors-session_list` - Permission: `view_doctors`
- `POST /api/save-doctor-session/{id}` - Permission: `edit_doctors`
- `POST /api/assign-doctor` - Permission: `edit_doctors`
- `POST /api/assign-doctor-service` - Permission: `edit_doctors`

### Receptionists
- `GET /api/get-receptionists` - Permission: `view_receptionists`
- `POST /api/save-receptionist` - Permission: `add_receptionists`
- `POST /api/update-receptionist/{id}` - Permission: `edit_receptionists`
- `POST /api/delete-receptionist/{id}` - Permission: `delete_receptionists`

### Patients
- `GET /api/get-patients` - Permission: `view_patients`
- `GET /api/get-patient-details` - Permission: `view_patients`

## Laboratory Module (`Modules/Laboratory/Routes/api.php`)

### Lab Tests
- `GET /api/lab-tests` - Permission: `view_lab_tests`
- `POST /api/lab-tests` - Permission: `create_lab_tests`
- `PUT /api/lab-tests/{id}` - Permission: `edit_lab_tests`
- `DELETE /api/lab-tests/{id}` - Permission: `delete_lab_tests`

### Lab Results
- `GET /api/lab-results` - Permission: `view_lab_results`
- `POST /api/lab-results` - Permission: `create_lab_results`
- `PUT /api/lab-results/{id}` - Permission: `edit_lab_results`
- `DELETE /api/lab-results/{id}` - Permission: `delete_lab_results`

### Lab Orders
- `GET /api/lab-orders` - Permission: `view_lab_orders`
- `POST /api/lab-orders` - Permission: `create_lab_orders`
- `PUT /api/lab-orders/{id}` - Permission: `edit_lab_orders`
- `DELETE /api/lab-orders/{id}` - Permission: `delete_lab_orders`

### Labs
- `GET /api/labs` - Permission: `view_labs`
- `POST /api/labs` - Permission: `create_labs`
- `PUT /api/labs/{id}` - Permission: `edit_labs`
- `DELETE /api/labs/{id}` - Permission: `delete_labs`

## Role-Based Access Summary

### Admin / Demo Admin
- Full access to all API endpoints
- Bypass all permission checks

### Doctor
- View/Edit own appointments
- View/Create/Edit patient encounters
- View/Create/Edit prescriptions
- View/Create medical reports
- View patients
- View own dashboard

### Receptionist
- View/Create/Edit appointments
- View patients
- View clinics/services
- View own dashboard

### Lab Technician
- View/Create/Edit lab tests
- View/Create/Edit lab results
- View/Edit lab orders
- View labs
- View own dashboard

### Vendor
- View/Edit own clinics
- View/Edit own services
- View/Edit own doctors
- View appointments for own clinics
- View own dashboard

### Pharma
- View/Edit medicines
- View/Edit prescriptions
- View own dashboard

### User (Patient)
- View own appointments
- View own encounters
- View own prescriptions
- View own medical reports
- Book appointments
- View clinics/doctors/services

## Implementation Notes

1. **Middleware Usage**: Apply `api.permission:permission_name` to routes
2. **Multiple Permissions**: Use `api.permission:perm1,perm2` for OR logic
3. **Admin Bypass**: Admin and demo_admin roles automatically bypass all checks
4. **Logging**: All permission checks are logged for debugging
5. **Error Response**: 403 with clear message when permission denied

## Testing Permissions

Use the test script `test_api_permissions.php` to verify permission enforcement.
