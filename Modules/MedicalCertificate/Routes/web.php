<?php

use Illuminate\Support\Facades\Route;
use Modules\MedicalCertificate\Http\Controllers\Backend\MedicalCertificateController;

Route::middleware(['auth', 'auth_check'])->group(function () {
    Route::prefix('medical-certificates')->as('medical-certificates.')->group(function () {
        Route::get('/', [MedicalCertificateController::class, 'index'])->name('index');
        Route::get('index_data', [MedicalCertificateController::class, 'index_data'])->name('index_data');
        Route::get('create', [MedicalCertificateController::class, 'create'])->name('create');
        Route::post('store', [MedicalCertificateController::class, 'store'])->name('store');
        Route::get('{id}', [MedicalCertificateController::class, 'show'])->name('show');
        Route::get('{id}/edit', [MedicalCertificateController::class, 'edit'])->name('edit');
        Route::put('{id}', [MedicalCertificateController::class, 'update'])->name('update');
        Route::delete('{id}', [MedicalCertificateController::class, 'destroy'])->name('destroy');
        Route::get('{id}/print', [MedicalCertificateController::class, 'print'])->name('print');
        Route::get('{id}/download', [MedicalCertificateController::class, 'download'])->name('download');
        Route::get('create-from-encounter/{encounter_id}', [MedicalCertificateController::class, 'createFromEncounter'])->name('create-from-encounter');
        Route::post('store-from-encounter/{encounter_id}', [MedicalCertificateController::class, 'storeFromEncounter'])->name('store-from-encounter');
    });
});
