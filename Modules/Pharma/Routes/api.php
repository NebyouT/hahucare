<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Pharma\Http\Controllers\API\MedicineController;
use Modules\Pharma\Http\Controllers\API\SupplierController;
use Modules\Pharma\Http\Controllers\API\PrescriptionController;
use Modules\Pharma\Http\Controllers\API\MedicineFormController;
use Modules\Pharma\Http\Controllers\API\MedicineCategoryController;
use Modules\Pharma\Http\Controllers\API\PharmaController; 
use Modules\Pharma\Http\Controllers\API\ManufracturerController;
use Modules\Pharma\Http\Controllers\API\OrderController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->name('api.')->group(function () {
    Route::get('pharma', fn (Request $request) => $request->user())->name('pharma');
    Route::get('medicine-usage-chart', [Modules\Pharma\Http\Controllers\Backend\PharmaController::class, 'getMedicineUsageChartData'])->name('medicine-usage-chart');


    Route::group(['prefix' => 'medicine', 'as' => 'medicine.'], function () {
        Route::get('list', [MedicineController::class, 'list'])->name('list');
        Route::get('category', [MedicineController::class, 'category'])->name('category');
        Route::get('supplier', [MedicineController::class, 'supplier'])->name('supplier');
        Route::get('manufacturer', [MedicineController::class, 'manufacturer'])->name('manufacturer');
        Route::post('store', [MedicineController::class, 'store'])->name('store');
        Route::post('add-extra-medicine/{id}', [MedicineController::class, 'addExtraMedicine'])->name('add_extra_medicine');
        Route::post('add-stock/{id}', [MedicineController::class, 'addStock'])->name('add-stock'); 
        Route::post('update/{id}', [MedicineController::class, 'update'])->name('update');
        Route::get('medicine-history', [MedicineController::class, 'medicineHistory'])->name('medicine-history');

    });

    Route::group(['prefix' => 'prescription', 'as' => 'prescription.'], function () {
        Route::get('list', [PrescriptionController::class, 'list'])->name('list');
        Route::get('detail', [PrescriptionController::class, 'detail'])->name('detail');
        Route::get('edit', [PrescriptionController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [PrescriptionController::class, 'update'])->name('update');
        Route::post('edit-medicine/{id}', [PrescriptionController::class, 'medicineEdit'])->name('medicie.edit');
        Route::delete('medicine-delete/{id}', [PrescriptionController::class, 'medicineDelete'])->name('medicie.delete');
        Route::post('update-prescription-status', [PrescriptionController::class, 'updatePrescriptionStatus'])->name('update_prescription_status');
        Route::post('update-prescription-payment-status', [PrescriptionController::class, 'updatePrescriptionPaymentStatus'])->name('update_prescription_payment_status');
       
    });

    Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
        Route::get('list', [SupplierController::class, 'list'])->name('list');
        Route::post('store', [SupplierController::class, 'store'])->name('store');
        Route::get('detail/{id}', [SupplierController::class, 'detail'])->name('detail');
        Route::delete('delete/{id}', [SupplierController::class, 'delete'])->name('delete');
        Route::get('type/list', [SupplierController::class, 'typeList'])->name('typelist');
    });
    
    Route::group(['prefix' => 'medicine-form', 'as' => 'medicine-form.'], function () {
        Route::get('list', [MedicineFormController::class, 'list'])->name('list');
        Route::post('store', [MedicineFormController::class, 'store'])->name('store');
        Route::get('edit', [MedicineFormController::class, 'edit'])->name('edit');
        Route::delete('delete/{id}', [MedicineFormController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'medicine-category', 'as' => 'medicine-category.'], function () {
        Route::get('list', [MedicineCategoryController::class, 'list'])->name('list');
        Route::post('store', [MedicineCategoryController::class, 'store'])->name('store');
        Route::get('edit', [MedicineCategoryController::class, 'edit'])->name('edit');
        Route::delete('delete/{id}', [MedicineCategoryController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'pharma', 'as' => 'pharma.'], function () {
        Route::get('payout-history', [PharmaController::class, 'payoutHistory'])->name('payout-history');
        Route::get('list-pharma', [PharmaController::class, 'listPharma'])->name('listPharma');
        Route::get('pharma-commission-list', [PharmaController::class, 'pharmaCommissionList']);
        Route::post('add-pharma', [Modules\Pharma\Http\Controllers\Backend\PharmaController::class, 'store'])->name('addPharma');
        Route::post('update-pharma/{id}', [Modules\Pharma\Http\Controllers\Backend\PharmaController::class, 'update'])->name('updatePharma');

    });

    Route::group(['prefix' => 'manufacturer', 'as' => 'manufacturer.'], function () {
        Route::get('list', [ManufracturerController::class, 'list'])->name('list');
        Route::get('detail', [ManufracturerController::class, 'detail'])->name('detail');
        Route::post('store', [ManufracturerController::class, 'store'])->name('store');
    });
    
    Route::group(['prefix' => 'order-medicine', 'as' => 'order-medicine.'], function () {
        Route::post('store', [OrderController::class, 'store'])->name('store');
        Route::get('purcheslist', [OrderController::class, 'purchesList'])->name('purcheslist');
        Route::post('update/{id}', [OrderController::class, 'update'])->name('update'); 
        Route::delete('delete/{id}', [OrderController::class, 'destroy'])->name('delete'); // <-- Add this line

    });
});