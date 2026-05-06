<?php

use Illuminate\Support\Facades\Route;
use Modules\PatientReferral\Http\Controllers\PatientReferralController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    
    // Patient Referral Routes with permission middleware
    Route::get('patientreferral', [PatientReferralController::class, 'index'])
        ->name('patientreferral.index')
        ->middleware('permission:view_patient_referral');
        
    // Advanced Referral Routes - MUST be before {id} routes to avoid conflicts
    Route::get('patientreferral/create-advanced', [PatientReferralController::class, 'createAdvanced'])
        ->name('patientreferral.create-advanced')
        ->middleware('permission:add_patient_referral');
        
    Route::post('patientreferral/store-advanced', [PatientReferralController::class, 'storeAdvanced'])
        ->name('patientreferral.store-advanced')
        ->middleware('permission:add_patient_referral');
        
    Route::get('patientreferral/{id}/edit-advanced', [PatientReferralController::class, 'editAdvanced'])
        ->name('patientreferral.edit-advanced')
        ->middleware('permission:edit_patient_referral');
        
    Route::put('patientreferral/{id}/update-advanced', [PatientReferralController::class, 'updateAdvanced'])
        ->name('patientreferral.update-advanced')
        ->middleware('permission:edit_patient_referral');

    // Standard Referral Routes
    Route::get('patientreferral/create', [PatientReferralController::class, 'create'])
        ->name('patientreferral.create')
        ->middleware('permission:add_patient_referral');
        
    Route::post('patientreferral', [PatientReferralController::class, 'store'])
        ->name('patientreferral.store')
        ->middleware('permission:add_patient_referral');
        
    Route::get('patientreferral/{id}', [PatientReferralController::class, 'show'])
        ->name('patientreferral.show')
        ->middleware('permission:view_patient_referral');
        
    Route::get('patientreferral/{id}/edit', [PatientReferralController::class, 'edit'])
        ->name('patientreferral.edit')
        ->middleware('permission:edit_patient_referral');
        
    Route::put('patientreferral/{id}', [PatientReferralController::class, 'update'])
        ->name('patientreferral.update')
        ->middleware('permission:edit_patient_referral');
        
    Route::delete('patientreferral/{id}', [PatientReferralController::class, 'destroy'])
        ->name('patientreferral.destroy')
        ->middleware('permission:delete_patient_referral');
        
    Route::post('patientreferral/{id}/accept', [PatientReferralController::class, 'acceptReferral'])
        ->name('patientreferral.accept')
        ->middleware('permission:edit_patient_referral');
        
    Route::get('patientreferral/{id}/book', [PatientReferralController::class, 'bookAppointment'])
        ->name('patientreferral.book')
        ->middleware('permission:edit_patient_referral');

    // PDF Download Route
    Route::get('patientreferral/{id}/pdf', [PatientReferralController::class, 'downloadPDF'])
        ->name('patientreferral.pdf')
        ->middleware('permission:view_patient_referral');
});

// API endpoint for patient data (no auth middleware for AJAX calls)
Route::get('api/patient-referral/patient-data/{patientId}', [PatientReferralController::class, 'getPatientData']);