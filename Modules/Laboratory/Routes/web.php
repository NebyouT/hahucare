<?php

use Illuminate\Support\Facades\Route;
use Modules\Laboratory\Http\Controllers\Backend\LabTestController;
use Modules\Laboratory\Http\Controllers\Backend\LabResultController;
use Modules\Laboratory\Http\Controllers\Backend\LabCategoryController;
use Modules\Laboratory\Http\Controllers\Backend\LabEquipmentController;

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
});
