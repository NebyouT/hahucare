<?php

use Illuminate\Support\Facades\Route;
use Modules\FAQ\Http\Controllers\FAQController;
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

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth','auth_check']], function () {
    /*
    * These routes need view-backend permission
    * (good if you want to allow more than one group in the backend,
    * then limit the backend features by different roles or permissions)
    *
    * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
    */

    /*
     *
     *  Backend FAQS Routes
     *
     * ---------------------------------------------------------------------
     */

    Route::group(['prefix' => 'faqs', 'as' => 'faqs.', 'middleware' => ['permission:view_faqs']], function () {
      Route::get("index_list", [FAQController::class, 'index_list'])->name("index_list");
      Route::get("index_data", [FAQController::class, 'index_data'])->name("index_data");
      Route::get('export', [FAQController::class, 'export'])->name('export')->middleware('permission:delete_faqs');
      Route::post('bulk-action', [FAQController::class, 'bulk_action'])->name('bulk_action')->middleware('permission:delete_faqs');
      Route::post('restore/{id}', [FAQController::class, 'restore'])->name('restore')->middleware('permission:delete_faqs');
      Route::delete('force-delete/{id}', [FAQController::class, 'forceDelete'])->name('force_delete')->middleware('permission:delete_faqs');
      Route::post('update-status/{id}', [FAQController::class, 'update_status'])->name('update_status')->middleware('permission:edit_faqs');
    });
    Route::resource("faqs", FAQController::class)->middleware([
        'index' => 'permission:view_faqs',
        'create' => 'permission:add_faqs',
        'store' => 'permission:add_faqs',
        'edit' => 'permission:edit_faqs',
        'update' => 'permission:edit_faqs',
        'destroy' => 'permission:delete_faqs',
    ]);
});
