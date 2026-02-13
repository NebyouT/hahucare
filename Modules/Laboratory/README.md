# Laboratory Management Module

## Overview

The Laboratory module is a comprehensive laboratory management system integrated into the HahuCare healthcare management platform. It provides complete functionality for managing laboratory tests, results, equipment, and categories.

## Features

### 1. Lab Test Management
- Create, edit, and delete laboratory tests
- Categorize tests for better organization
- Set pricing and discount information
- Define test duration and reporting time
- Specify sample types and preparation instructions
- Set normal ranges and units of measurement
- Mark tests as active/inactive or featured

### 2. Lab Test Categories
- Organize tests into logical categories
- Manage category hierarchy
- Track number of tests per category
- Enable/disable categories

### 3. Lab Results Management
- Record and track lab test results
- Link results to patients, doctors, and appointments
- Track result status (pending, in_progress, completed, cancelled)
- Assign lab technicians to results
- Store result values and remarks
- Upload and manage result attachments
- Print and download result reports

### 4. Lab Equipment Management
- Maintain equipment inventory
- Track equipment status (active, maintenance, inactive, retired)
- Record manufacturer and model information
- Schedule and track maintenance dates
- Monitor warranty expiration
- Track equipment location

## Database Schema

### Tables Created

1. **lab_test_categories** - Test categorization
2. **lab_tests** - Laboratory test definitions
3. **lab_results** - Test results and patient data
4. **lab_equipment** - Equipment inventory

All tables include:
- Soft deletes for data retention
- Audit fields (created_by, updated_by, deleted_by)
- Timestamps

## Permissions

The module includes comprehensive role-based permissions:

### Lab Tests
- `view_lab_tests`
- `create_lab_tests`
- `edit_lab_tests`
- `delete_lab_tests`
- `export_lab_tests`

### Lab Results
- `view_lab_results`
- `create_lab_results`
- `edit_lab_results`
- `delete_lab_results`
- `approve_lab_results`
- `print_lab_results`

### Lab Categories
- `view_lab_categories`
- `create_lab_categories`
- `edit_lab_categories`
- `delete_lab_categories`

### Lab Equipment
- `view_lab_equipment`
- `create_lab_equipment`
- `edit_lab_equipment`
- `delete_lab_equipment`

## Role Assignments

- **Admin**: Full access to all laboratory features
- **Lab Technician**: Can view tests, manage results, view equipment
- **Doctor**: Can view tests and results, create results
- **Receptionist**: Can view tests and results, create results

## Installation

### 1. Run Migrations

```bash
php artisan module:migrate Laboratory
```

### 2. Seed Permissions

```bash
php artisan db:seed --class=Modules\\Laboratory\\Database\\Seeders\\LaboratoryPermissionsSeeder
```

### 3. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 4. Regenerate Autoload

```bash
composer dump-autoload
```

## Routes

All routes are prefixed with `/app` and require authentication:

### Lab Tests
- `GET /app/lab-tests` - List all tests
- `GET /app/lab-tests/create` - Create test form
- `POST /app/lab-tests` - Store new test
- `GET /app/lab-tests/{id}/edit` - Edit test form
- `PUT /app/lab-tests/{id}` - Update test
- `DELETE /app/lab-tests/{id}` - Delete test
- `GET /app/lab-tests/index_data` - DataTables data
- `POST /app/lab-tests/update-status/{id}` - Toggle status
- `POST /app/lab-tests/bulk-action` - Bulk operations

### Lab Results
- `GET /app/lab-results` - List all results
- `GET /app/lab-results/create` - Create result form
- `POST /app/lab-results` - Store new result
- `GET /app/lab-results/{id}` - View result
- `GET /app/lab-results/{id}/edit` - Edit result form
- `PUT /app/lab-results/{id}` - Update result
- `DELETE /app/lab-results/{id}` - Delete result
- `GET /app/lab-results/index_data` - DataTables data
- `POST /app/lab-results/update-status` - Update status
- `GET /app/lab-results/print/{id}` - Print result
- `GET /app/lab-results/download/{id}` - Download PDF

### Lab Categories
- `GET /app/lab-categories` - List all categories
- `GET /app/lab-categories/create` - Create category form
- `POST /app/lab-categories` - Store new category
- `GET /app/lab-categories/{id}/edit` - Edit category form
- `PUT /app/lab-categories/{id}` - Update category
- `DELETE /app/lab-categories/{id}` - Delete category
- `GET /app/lab-categories/index_data` - DataTables data
- `POST /app/lab-categories/update-status/{id}` - Toggle status
- `POST /app/lab-categories/bulk-action` - Bulk operations

### Lab Equipment
- `GET /app/lab-equipment` - List all equipment
- `GET /app/lab-equipment/create` - Create equipment form
- `POST /app/lab-equipment` - Store new equipment
- `GET /app/lab-equipment/{id}/edit` - Edit equipment form
- `PUT /app/lab-equipment/{id}` - Update equipment
- `DELETE /app/lab-equipment/{id}` - Delete equipment
- `GET /app/lab-equipment/index_data` - DataTables data
- `POST /app/lab-equipment/update-status/{id}` - Update status
- `POST /app/lab-equipment/bulk-action` - Bulk operations

## Models

### LabTest
- Relationships: `category()`, `results()`
- Scopes: `active()`, `featured()`
- Attributes: `final_price`

### LabTestCategory
- Relationships: `labTests()`
- Scopes: `active()`

### LabResult
- Relationships: `labTest()`, `patient()`, `doctor()`, `technician()`
- Scopes: `pending()`, `completed()`, `inProgress()`

### LabEquipment
- Scopes: `active()`, `needsMaintenance()`

## Integration Points

### With Appointment Module
Lab results can be linked to appointments via the `appointment_id` field.

### With User Module
Lab results are linked to:
- Patients (patient_id)
- Doctors (doctor_id)
- Lab Technicians (technician_id)

## Views

All views extend the backend layout (`backend.layouts.app`) and include:
- DataTables for listing data
- AJAX-based CRUD operations
- Status toggles
- Bulk actions
- Search and filtering

## Future Enhancements

1. **PDF Report Generation** - Generate professional PDF reports for lab results
2. **Email Notifications** - Notify patients when results are ready
3. **Test Packages** - Bundle multiple tests together
4. **Reference Ranges** - Define age/gender-specific normal ranges
5. **Result Templates** - Pre-defined templates for common tests
6. **Equipment Calibration** - Track equipment calibration schedules
7. **Quality Control** - QC sample tracking and validation
8. **Integration with Lab Instruments** - Direct data import from lab equipment
9. **Statistical Reports** - Analytics and reporting dashboards
10. **Mobile App Support** - API endpoints for mobile applications

## Support

For issues or questions, please contact the development team or refer to the main HahuCare documentation.

## Version

- **Version**: 1.0.0
- **Created**: January 27, 2026
- **Laravel Version**: 11.x
- **PHP Version**: 8.2+

## License

This module is part of the HahuCare healthcare management system.
