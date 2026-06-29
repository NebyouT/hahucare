<?php

use Illuminate\Support\Facades\Route;
use Modules\PatientReferral\Http\Controllers\API\PatientReferralAPIController;

Route::get('patient-referrals', [PatientReferralAPIController::class, 'index'])->middleware('auth:sanctum');
Route::get('patient-referral-detail', [PatientReferralAPIController::class, 'show'])->middleware('auth:sanctum');
