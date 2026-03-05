<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Backend\API\AppointmentsController;
use Modules\Appointment\Http\Controllers\Backend\API\PatientEncounterController;
use Modules\Appointment\Http\Controllers\Backend\API\EncounterDashboardController;



Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::post('save-booking', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'store'])->middleware('api.permission:add_appointment');
    Route::post('update-booking/{id}', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'update'])->middleware('api.permission:edit_appointment');
    Route::get('appointment-list', [AppointmentsController::class, 'appointmentList'])->middleware('api.permission:view_appointment');
    Route::get('appointment-detail', [AppointmentsController::class, 'appointmentDetails'])->middleware('api.permission:view_appointment');
    Route::post('save-payment', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'savePayment'])->middleware('api.permission:edit_appointment');
    Route::post('update-status/{id}', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'updateStatus'])->middleware('api.permission:edit_appointment');
    Route::post('reschedule-booking', [AppointmentsController::class, 'rescheduleBooking'])->middleware('api.permission:edit_appointment');
    Route::post('cancel-appointment/{id}', [AppointmentsController::class, 'cancelAppointment'])->middleware('api.permission:delete_appointment');

    Route::get('encounter-list', [PatientEncounterController::class, 'encounterList'])->middleware('api.permission:view_encounter');
    Route::post('save-encounter', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'store'])->middleware('api.permission:add_encounter');
    Route::post('update-encounter/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'update'])->middleware('api.permission:edit_encounter');
    Route::post('delete-encounter/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'destroy'])->middleware('api.permission:delete_encounter');
    Route::get('encounter-details', [PatientEncounterController::class, 'encounterList'])->middleware('api.permission:view_encounter');
    Route::get('download-encounter-invoice', [PatientEncounterController::class, 'encounterInvoice'])->middleware('api.permission:view_encounter');
    Route::get('download-prescription', [PatientEncounterController::class, 'downloadPrescription'])->middleware('api.permission:view_encounter');

    Route::get('encounter-dropdown-list', [EncounterDashboardController::class, 'encounterDropdownList']);

    Route::get('get-medical-report', [EncounterDashboardController::class, 'GetMedicalReport'])->middleware('api.permission:view_medical_report');
    Route::post('save-medical-report', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'saveMedicalReport'])->middleware('api.permission:add_medical_report');

    Route::post('update-medical-report/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'updateMedicalReport'])->middleware('api.permission:edit_medical_report');
    Route::get('delete-medical-report/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'deleteMedicalReport'])->middleware('api.permission:delete_medical_report');

    Route::get('get-prescription', [EncounterDashboardController::class, 'GetPrescription'])->middleware('api.permission:view_prescription');
    Route::post('save-prescription', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'saveMedicalReport'])->middleware('api.permission:add_prescription');

    Route::post('update-prescription/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'updateMedicalReport'])->middleware('api.permission:edit_prescription');
    Route::get('delete-prescription/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'deleteMedicalReport'])->middleware('api.permission:delete_prescription');

    Route::post('save-encounter-dashboard', [EncounterDashboardController::class, 'saveEncounterDashboard']);
    Route::get('encounter-dashboard-detail', [EncounterDashboardController::class, 'encounterDashboardDetail']);
    Route::get('encounter-service-detail', [EncounterDashboardController::class, 'encounterServiceDetails']);

    Route::get('download_invoice', [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'downloadPDf'])->name('download_invoice');

    Route::post("save-bodychart", [EncounterDashboardController::class, 'saveBodychart']);
    Route::post("update-bodychart/{id}", [EncounterDashboardController::class, 'updateBodychart']);
    Route::post('delete-bodychart/{id}', [EncounterDashboardController::class, 'deleteBodychart']);
    Route::get('bodychart-list', [EncounterDashboardController::class, 'bodyChartList']);

    Route::post('save-billing-details', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'saveBillingDetails'])->middleware('api.permission:add_billing');
    Route::get('billing-list', [EncounterDashboardController::class, 'billingList'])->middleware('api.permission:view_billing');
    Route::get('billing-record-detail', [EncounterDashboardController::class, 'billingList'])->middleware('api.permission:view_billing');

    Route::get('get-revenue-chart-data', [AppointmentsController::class, 'getRevenuechartData']);
    Route::post("save-soap/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient'])->name("appointment_patient");
    Route::get("get-soap/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient_data'])->name("appointment_patient_data");
    Route::put("save-appointment_patient/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient'])->name("appointment_patient");
    Route::get("{id}/get_appointment_patient_data", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient_data'])->name("appointment_patient_data");

    Route::post('/save-billing-items', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'saveBillingItems'])->middleware('api.permission:add_billing');
    Route::get('billing-item-list', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'billing_item_list'])->name('billing_item_list')->middleware('api.permission:view_billing');
    Route::get('billing-item-details', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'billing_item_detail'])->name('billing_item_detail')->middleware('api.permission:view_billing');
    Route::get('edit-billing-item/{id}', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'editBillingItem'])->middleware('api.permission:edit_billing');
    Route::post('delete-billing-item/{id}', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'deleteBillingItem'])->middleware('api.permission:delete_billing');
});
