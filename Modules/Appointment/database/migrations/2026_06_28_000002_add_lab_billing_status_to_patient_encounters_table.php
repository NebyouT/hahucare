<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_encounters', function (Blueprint $table) {
            $table->tinyInteger('lab_billing_status')->default(0)->after('prescription_payment_status')->comment('0 = Unpaid, 1 = Paid');
        });
    }

    public function down(): void
    {
        Schema::table('patient_encounters', function (Blueprint $table) {
            $table->dropColumn('lab_billing_status');
        });
    }
};
