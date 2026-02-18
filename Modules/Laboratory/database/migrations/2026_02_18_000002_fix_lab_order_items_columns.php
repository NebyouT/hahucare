<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_order_items', function (Blueprint $table) {
            // Make lab_test_id nullable (we use lab_service_id now)
            if (Schema::hasColumn('lab_order_items', 'lab_test_id')) {
                $table->unsignedBigInteger('lab_test_id')->nullable()->change();
            }

            // Make test_name nullable (we use service_name now)
            if (Schema::hasColumn('lab_order_items', 'test_name')) {
                $table->string('test_name')->nullable()->change();
            }

            // Add service_name if missing
            if (!Schema::hasColumn('lab_order_items', 'service_name')) {
                $table->string('service_name')->nullable()->after('lab_service_id');
            }

            // Add service_description if missing
            if (!Schema::hasColumn('lab_order_items', 'service_description')) {
                $table->text('service_description')->nullable()->after('service_name');
            }

            // Add urgent_flag if missing
            if (!Schema::hasColumn('lab_order_items', 'urgent_flag')) {
                $table->boolean('urgent_flag')->default(false)->after('service_description');
            }

            // Add clinical_notes if missing
            if (!Schema::hasColumn('lab_order_items', 'clinical_notes')) {
                $table->text('clinical_notes')->nullable()->after('urgent_flag');
            }

            // Add sample_type if missing
            if (!Schema::hasColumn('lab_order_items', 'sample_type')) {
                $table->string('sample_type')->nullable()->after('clinical_notes');
            }

            // Add fasting_required if missing
            if (!Schema::hasColumn('lab_order_items', 'fasting_required')) {
                $table->boolean('fasting_required')->default(false)->after('sample_type');
            }

            // Add special_instructions if missing
            if (!Schema::hasColumn('lab_order_items', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('fasting_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_order_items', function (Blueprint $table) {
            $table->dropColumn(['service_name', 'service_description', 'urgent_flag', 'clinical_notes', 'sample_type', 'fasting_required', 'special_instructions']);
        });
    }
};
