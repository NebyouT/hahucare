<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if column exists before adding
        if (!Schema::hasColumn('lab_tests', 'lab_id')) {
            Schema::table('lab_tests', function (Blueprint $table) {
                $table->unsignedBigInteger('lab_id')->nullable()->after('category_id');
                $table->foreign('lab_id')->references('id')->on('labs')->onDelete('set null');
                $table->index('lab_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lab_tests', 'lab_id')) {
            Schema::table('lab_tests', function (Blueprint $table) {
                $table->dropForeign(['lab_id']);
                $table->dropIndex(['lab_id']);
                $table->dropColumn('lab_id');
            });
        }
    }
};
