<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Laboratory\Http\Controllers\Backend\API\LabOrderAPIController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('laboratory', fn (Request $request) => $request->user())->name('laboratory');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('lab-orders', [LabOrderAPIController::class, 'myLabOrders']);
    Route::get('lab-order-detail', [LabOrderAPIController::class, 'labOrderDetail']);
});
