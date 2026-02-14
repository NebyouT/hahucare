<?php

use Illuminate\Support\Facades\Route;
use Modules\Clinic\Http\Controllers\ClinicsServiceController;
use Modules\Clinic\Http\Controllers\ClinicesController;
use Modules\Customer\Http\Controllers\Backend\CustomersController;
use Modules\Pharma\Http\Controllers\Backend\BillingRecordController;
use Modules\Pharma\Http\Controllers\Backend\EarningController;
use Modules\Pharma\Http\Controllers\Backend\ExpiredMedicineController;
use Modules\Pharma\Http\Controllers\Backend\ManufacturerController;
use Modules\Pharma\Http\Controllers\Backend\MedicineCategoryController;
use Modules\Pharma\Http\Controllers\Backend\MedicineController;
use Modules\Pharma\Http\Controllers\Backend\MedicineFormController;
use Modules\Pharma\Http\Controllers\Backend\OrderController;
use Modules\Pharma\Http\Controllers\Backend\PharmaController;
use Modules\Pharma\Http\Controllers\Backend\PharmaPayoutController;
use Modules\Pharma\Http\Controllers\Backend\PrescriptionController;
use Modules\Pharma\Http\Controllers\Backend\SupplierController;
use Modules\Pharma\Http\Controllers\Backend\SupplierTypeController;
use Modules\Tax\Http\Controllers\Backend\TaxesController;

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

Route::group([], function () {

});

Route::post('/check-email-exists', [PharmaController::class, 'checkEmail'])->name('check-email-exists');
Route::post('/check-contact-exists', [PharmaController::class, 'checkContact'])->name('check-contact-exists');




Route::group(['prefix' => 'app/supplier-type', 'middleware' => ['auth', 'auth_check', 'pharmaStatus']], function () {
    Route::get('services/index_list', [ClinicsServiceController::class, 'index_list'])->name('backend.supplier-type.services.index_list');
    Route::get('clinics/index_list', [ClinicesController::class, 'index_list'])->name('backend.supplier-type.clinics.index_list');
    Route::get('customers/index_list', [CustomersController::class, 'index_list'])->name('backend.supplier-type.customers.index_list');
    Route::get('tax/index_list', [TaxesController::class, 'index_list'])->name('backend.supplier-type.tax.index_list');
});

Route::group(['prefix' => 'app/medicine-form', 'middleware' => ['auth', 'auth_check', 'pharmaStatus']], function () {
    Route::get('services/index_list', [ClinicsServiceController::class, 'index_list'])->name('backend.medicine-form.services.index_list');
    Route::get('clinics/index_list', [ClinicesController::class, 'index_list'])->name('backend.medicine-form.clinics.index_list');
    Route::get('customers/index_list', [CustomersController::class, 'index_list'])->name('backend.medicine-form.customers.index_list');
    Route::get('tax/index_list', [TaxesController::class, 'index_list'])->name('backend.medicine-form.tax.index_list');
});

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check', 'pharmaStatus']], function () {


    Route::group(['prefix' => 'medicine', 'as' => 'medicine.'], function () {
        Route::get('index_data', [MedicineController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [MedicineController::class, 'bulk_action'])->name('bulk_action');
        Route::get('export', [MedicineController::class, 'export'])->name('export');
        Route::post('update-status/{id}', [MedicineController::class, 'update_status'])->name('update_status');
        Route::get('medicine-detail-table', [MedicineController::class, 'medicineDetailTable'])->name('medicine-detail-table');
        Route::get('medicine-details/{medicine_id}/{supplier_id}', [MedicineController::class, 'medicineDetails'])->name('medicine-details');
        Route::post('add-stock', [MedicineController::class, 'addStock'])->name('add_stock');
        Route::get('medicine-history/{id}', [MedicineController::class, 'getMedicineHistory'])->name('history');
        Route::get('services/index_list', [ClinicsServiceController::class, 'index_list'])->name('services.index_list');
        Route::get('clinics/index_list', [ClinicesController::class, 'index_list'])->name('clinics.index_list');
        Route::get('customers/index_list', [CustomersController::class, 'index_list'])->name('customers.index_list');
        Route::get('tax/index_list', [TaxesController::class, 'index_list'])->name('tax.index_list');
    });
    Route::resource('medicine', MedicineController::class);

    Route::group(['prefix' => 'order-medicine', 'as' => 'order-medicine.'], function () {
        Route::get('index_data', [OrderController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [OrderController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-payment-status', [OrderController::class, 'updatePaymentStatus'])->name('update_payment_status');
        Route::post('update-order-status', [OrderController::class, 'updateOrderStatus'])->name('update_order_status');
        Route::get('export', [OrderController::class, 'export'])->name('export');

    });
    Route::get('order-medicine/create/{medicineId}', [OrderController::class, 'create'])->name('order-medicine.create.with-medicine');
    Route::resource('order-medicine', OrderController::class);

    Route::group(['prefix' => 'expired-medicine', 'as' => 'expired-medicine.'], function () {
        Route::get('index_data', [ExpiredMedicineController::class, 'index_data'])->name('index_data');
        Route::get('export', [ExpiredMedicineController::class, 'export'])->name('export');
        Route::get('expired-medicine-details/{medicine_id}/{supplier_id}', [ExpiredMedicineController::class, 'medicineDetails'])->name('medicine-details');
    });
    Route::resource('expired-medicine', ExpiredMedicineController::class);

    Route::post('manufacturers/store', [ManufacturerController::class, 'store'])->name('manufacturers.store');
    Route::group(['prefix' => 'medicine-category', 'as' => 'medicine-category.'], function () {
        Route::get('index_data', [MedicineCategoryController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [MedicineCategoryController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [MedicineCategoryController::class, 'update_status'])->name('update_status');
        Route::get('export', [MedicineCategoryController::class, 'export'])->name('export');
    });
    Route::resource('medicine-category', MedicineCategoryController::class);

    Route::group(['prefix' => 'medicine-form', 'as' => 'medicine-form.'], function () {
        Route::get('index_data', [MedicineFormController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [MedicineFormController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [MedicineFormController::class, 'update_status'])->name('update_status');
        Route::get('export', [MedicineFormController::class, 'export'])->name('export');
    });
    Route::resource('medicine-form', MedicineFormController::class);
    Route::group(['prefix' => 'supplier-type', 'as' => 'supplier-type.'], function () {
        Route::get('index_data', [SupplierTypeController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [SupplierTypeController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [SupplierTypeController::class, 'update_status'])->name('update_status');
        Route::get('export', [SupplierTypeController::class, 'export'])->name('export');
        Route::get('export-preview', [SupplierTypeController::class, 'exportPreview'])->name('export-preview');
    });
    Route::resource('supplier-type', SupplierTypeController::class);

    Route::group(['prefix' => 'suppliers', 'as' => 'suppliers.'], function () {
        Route::get('index_data', [SupplierController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [SupplierController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [SupplierController::class, 'update_status'])->name('update_status');
        Route::get('supplier-info', [SupplierController::class, 'supplierInfo'])->name('supplier-info');
        Route::post('check-email', [SupplierController::class, 'checkEmail'])->name('check-email');
        Route::post('check-contact', [SupplierController::class, 'checkContact'])->name('check-contact');
        Route::get('export', [SupplierController::class, 'export'])->name('export');
    });
    Route::resource('suppliers', SupplierController::class);

    Route::group(['prefix' => 'prescription', 'as' => 'prescription.'], function () {
        Route::get('index_data', [PrescriptionController::class, 'index_data'])->name('index_data');
        Route::post('update-prescription-status', [PrescriptionController::class, 'updatePrescriptionStatus'])->name('update_prescription_status');
        Route::post('update-prescription-payment-status', [PrescriptionController::class, 'updatePrescriptionPaymentStatus'])->name('update_prescription_payment_status');
        Route::get('user-prescription-detail', [PrescriptionController::class, 'userPrescriptionDetail'])->name('user_prescription_detail');
        Route::get('add-extra-medicine/{id}', [PrescriptionController::class, 'addExtraMedicine'])->name('add_extra_medicine');
        Route::post('save-extra-medicine/{id}', [PrescriptionController::class, 'saveExtraMedicine'])->name('save_extra_medicine');
        Route::post('update-extra-medicine/{id}', [PrescriptionController::class, 'patientPrescriptionUpdate'])->name('update_extra_medicine');
        Route::get('patient-prescription/edit/{id}', [PrescriptionController::class, 'patientPrescriptionEdit'])->name('patient-prescription.edit');
        Route::delete('patient-prescription/{id}', [PrescriptionController::class, 'patientPrescriptionDelete'])->name('patient-prescription.destroy');
        Route::get('payment-detail/{id}', [PrescriptionController::class, 'getPaymentDetailHtml'])->name('payment_detail');
        Route::get('show-pharma-info/{id}', [PrescriptionController::class, 'getmedicineDetailDetailHtml'])->name('show-pharma-info');
        Route::get('medicine-stock/{id}', [PrescriptionController::class, 'getMedicineStock'])->name('medicine_stock');
        Route::post('bulk-action', [PrescriptionController::class, 'bulk_action'])->name('bulk_action');
        Route::post('encounter-bulk-action', [PrescriptionController::class, 'encounterBulkAction'])->name('encounter_bulk_action');
        Route::get('export', [PrescriptionController::class, 'export'])->name('export');

    });
    Route::resource('prescription', PrescriptionController::class);

    Route::group(['prefix' => 'pharma', 'as' => 'pharma.'], function () {

        Route::get('index_data', [PharmaController::class, 'index_data'])->name('index_data');
        Route::post('bulk_action', [PharmaController::class, 'bulk_action'])->name('bulk_action');
        Route::get('detail/{id}', [PharmaController::class, 'pharmaDetail'])->name('detail');
        Route::post('update-status/{id}', [PharmaController::class, 'updateStatus'])->name('update-status');
        Route::get('change-password/{id}', [PharmaController::class, 'changePassword'])->name('change-password');
        Route::post('update-password/{id}', [PharmaController::class, 'updatePassword'])->name('update-password');
        Route::get('export', [PharmaController::class, 'export'])->name('export');

        Route::get('services/index_list', [ClinicsServiceController::class, 'index_list'])->name('services.index_list');
        Route::get('clinics/index_list', [ClinicesController::class, 'index_list'])->name('clinics.index_list');
        Route::get('customers/index_list', [CustomersController::class, 'index_list'])->name('customers.index_list');
        Route::get('tax/index_list', [TaxesController::class, 'index_list'])->name('tax.index_list');

        Route::group(['prefix' => 'billing-records', 'as' => 'billing-records.'], function () {
            Route::get('index_data', [BillingRecordController::class, 'index_data'])->name('index_data');
            Route::get('billing-detail/{id}', [BillingRecordController::class, 'billing_detail'])->name('billing_detail');
        });
        Route::resource('billing-records', BillingRecordController::class);

        Route::get('/get_revnue_chart_data/{type}', [PharmaController::class, 'getRevenuechartData']);
        Route::get('/get-medicine-usage-chart', [PharmaController::class, 'getMedicineUsageChartData'])->name('medicine-usage-chart');

        Route::get('/earnings-chart-data', [PharmaController::class, 'getEarningsChartData'])->name('earnings-chart');
        Route::get('/pharma-verify/{id}', [PharmaController::class, 'verifyPharma'])->name('verify-pharma');

    });
    Route::resource('pharma', PharmaController::class)->names('pharma');

    Route::group(['prefix' => 'payout', 'as' => 'payout.'], function () {

        Route::get('index_data', [PharmaPayoutController::class, 'index_data'])->name('index_data');
        Route::get('pharma-payout-report', [PharmaPayoutController::class, 'pharmaPayoutReport'])->name('pharma-payout-report');
        Route::get('pharma-payout-report-index-data', [PharmaPayoutController::class, 'pharmaPayoutReportIndexData'])->name('pharma-payout-report.index_data');

    });
    Route::resource('payout', PharmaPayoutController::class);

    Route::group(['prefix' => 'earning', 'as' => 'earning.'], function () {
        Route::get('pharma-earnings', [EarningController::class, 'index'])->name('index');
        Route::get('pharma-earnings-index-data', [EarningController::class, 'index_data'])->name('index_data');
        Route::get('payout-details/{id}', [EarningController::class, 'payoutDetails'])->name('payout_details');
        Route::get('view-commission-detail/{id}', [EarningController::class, 'viewCommissionDetail'])->name('view.commission.detail');

    });


    Route::resource('earning', EarningController::class);

});
