<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_order_billing_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('encounter_id')->nullable();
            $table->json('exclusive_tax')->nullable();
            $table->string('exclusive_tax_amount')->nullable();
            $table->string('total_amount')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_billing_details');
    }
};
