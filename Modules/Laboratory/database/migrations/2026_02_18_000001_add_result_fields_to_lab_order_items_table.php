<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_order_items', 'result_file')) {
                $table->string('result_file')->nullable()->after('status');
            }
            if (!Schema::hasColumn('lab_order_items', 'technician_note')) {
                $table->text('technician_note')->nullable()->after('result_file');
            }
            if (!Schema::hasColumn('lab_order_items', 'result_uploaded_at')) {
                $table->timestamp('result_uploaded_at')->nullable()->after('technician_note');
            }
            if (!Schema::hasColumn('lab_order_items', 'lab_service_id')) {
                $table->unsignedBigInteger('lab_service_id')->nullable()->after('lab_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_order_items', function (Blueprint $table) {
            $table->dropColumn(['result_file', 'technician_note', 'result_uploaded_at']);
        });
    }
};
