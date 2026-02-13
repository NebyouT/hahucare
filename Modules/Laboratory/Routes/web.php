<?php

use Illuminate\Support\Facades\Route;
use Modules\Laboratory\Http\Controllers\Backend\LabTestController;
use Modules\Laboratory\Http\Controllers\Backend\LabResultController;
use Modules\Laboratory\Http\Controllers\Backend\LabCategoryController;
use Modules\Laboratory\Http\Controllers\Backend\LabEquipmentController;
use Modules\Laboratory\Http\Controllers\Backend\LabController;
use Modules\Laboratory\Http\Controllers\Backend\LabOrderController;
use Modules\Laboratory\Http\Controllers\Backend\LabServiceController;
use Modules\Laboratory\Http\Controllers\LabTestOrderController;

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
    
    // Lab Tests
    Route::group(['prefix' => 'lab-tests', 'as' => 'lab-tests.'], function () {
        Route::get('index_data', [LabTestController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabTestController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabTestController::class, 'update_status'])->name('update_status');
        Route::get('export', [LabTestController::class, 'export'])->name('export');
    });
    Route::resource('lab-tests', LabTestController::class);

    // Lab Results
    Route::group(['prefix' => 'lab-results', 'as' => 'lab-results.'], function () {
        Route::get('index_data', [LabResultController::class, 'index_data'])->name('index_data');
        Route::post('update-status', [LabResultController::class, 'updateStatus'])->name('update_status');
        Route::get('print/{id}', [LabResultController::class, 'print'])->name('print');
        Route::get('download/{id}', [LabResultController::class, 'download'])->name('download');
        Route::post('upload-attachment/{id}', [LabResultController::class, 'uploadAttachment'])->name('upload_attachment');
        Route::delete('remove-attachment/{id}', [LabResultController::class, 'removeAttachment'])->name('remove_attachment');
    });
    Route::resource('lab-results', LabResultController::class);

    // Lab Categories
    Route::group(['prefix' => 'lab-categories', 'as' => 'lab-categories.'], function () {
        Route::get('index_data', [LabCategoryController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabCategoryController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabCategoryController::class, 'update_status'])->name('update_status');
    });
    Route::resource('lab-categories', LabCategoryController::class);

    // Lab Equipment
    Route::group(['prefix' => 'lab-equipment', 'as' => 'lab-equipment.'], function () {
        Route::get('index_data', [LabEquipmentController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabEquipmentController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabEquipmentController::class, 'update_status'])->name('update_status');
    });
    Route::resource('lab-equipment', LabEquipmentController::class);

    // Labs
    Route::group(['prefix' => 'labs', 'as' => 'labs.'], function () {
        Route::get('index_data', [LabController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabController::class, 'update_status'])->name('update_status');
        Route::get('get-by-clinic/{clinic_id}', [LabController::class, 'getLabsByClinic'])->name('get_by_clinic');
    });
    Route::resource('labs', LabController::class);

    // Lab Services
    Route::group(['prefix' => 'lab-services', 'as' => 'lab-services.'], function () {
        Route::get('index_data', [LabServiceController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabServiceController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabServiceController::class, 'update_status'])->name('update_status');
        Route::get('get-by-category/{category_id}', [LabServiceController::class, 'getServicesByCategory'])->name('get_by_category');
        Route::get('get-by-lab/{lab_id}', [LabServiceController::class, 'getServicesByLab'])->name('get_by_lab');
    });
    Route::resource('lab-services', LabServiceController::class);

    // Lab Orders
    Route::group(['prefix' => 'lab-orders', 'as' => 'lab-orders.'], function () {
        Route::get('index_data', [LabOrderController::class, 'index_data'])->name('index_data');
        Route::get('get-tests/{lab_id}', [LabOrderController::class, 'getLabTests'])->name('get_tests');
        Route::get('get-labs-by-clinic/{clinic_id}', [LabOrderController::class, 'getLabsByClinic'])->name('get_labs_by_clinic');
        Route::get('get-services-by-lab/{lab_id}', [LabOrderController::class, 'getServicesByLab'])->name('get_services_by_lab');
        Route::get('get-doctors-by-clinic/{clinic_id}', [LabOrderController::class, 'getDoctorsByClinic'])->name('get_doctors_by_clinic');
        Route::get('get-patients-by-doctor/{doctor_id}', [LabOrderController::class, 'getPatientsByDoctor'])->name('get_patients_by_doctor');
    });
    Route::resource('lab-orders', LabOrderController::class);
});

// Lab Test Ordering from Encounters
Route::group(['prefix' => 'lab-orders', 'middleware' => ['auth']], function () {
    Route::get('create-from-encounter/{encounter_id}', [LabTestOrderController::class, 'create'])->name('lab_orders.create_from_encounter');
    Route::post('store-from-encounter/{encounter_id}', [LabTestOrderController::class, 'store'])->name('lab_orders.store_from_encounter');
    Route::get('get-by-encounter/{encounter_id}', [LabTestOrderController::class, 'getOrdersByEncounter'])->name('lab_orders.get_by_encounter');
    Route::get('show/{order_id}', [LabTestOrderController::class, 'show'])->name('lab_orders.show');
});

// API Routes for Lab Test Ordering
Route::group(['prefix' => 'api/lab-orders', 'middleware' => ['auth']], function () {
    Route::get('get-labs-by-clinic/{clinic_id}', [LabTestOrderController::class, 'getLabsByClinic']);
    Route::get('get-tests-by-category-and-lab/{category_id}/{lab_id}', [LabTestOrderController::class, 'getTestsByCategoryAndLab']);
});

// API Routes for Frontend
Route::group(['prefix' => 'api', 'middleware' => ['web']], function () {
    Route::get('/lab-tests/by-category/{category_id}', [LabTestController::class, 'getTestsByCategory']);
});
