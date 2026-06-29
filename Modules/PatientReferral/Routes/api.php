<?php

use Illuminate\Support\Facades\Route;
use Modules\PatientReferral\Http\Controllers\API\PatientReferralAPIController;

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('patient-referrals', [PatientReferralAPIController::class, 'index'])->name('patientreferral.list');
    Route::get('patient-referral-detail', [PatientReferralAPIController::class, 'show'])->name('patientreferral.detail');
});
