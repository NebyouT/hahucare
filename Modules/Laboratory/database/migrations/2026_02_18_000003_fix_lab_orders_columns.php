<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_orders', 'order_type')) {
                $table->string('order_type')->default('outpatient')->after('encounter_id');
            }
            if (!Schema::hasColumn('lab_orders', 'priority')) {
                $table->string('priority')->default('routine')->after('order_type');
            }
            if (!Schema::hasColumn('lab_orders', 'clinical_indication')) {
                $table->text('clinical_indication')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('lab_orders', 'diagnosis_suspected')) {
                $table->string('diagnosis_suspected')->nullable()->after('clinical_indication');
            }
            if (!Schema::hasColumn('lab_orders', 'referred_by')) {
                $table->unsignedBigInteger('referred_by')->nullable()->after('diagnosis_suspected');
            }
            if (!Schema::hasColumn('lab_orders', 'department')) {
                $table->string('department')->nullable()->after('referred_by');
            }
            if (!Schema::hasColumn('lab_orders', 'ward_room')) {
                $table->string('ward_room')->nullable()->after('department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'priority', 'clinical_indication', 'diagnosis_suspected', 'referred_by', 'department', 'ward_room']);
        });
    }
};
