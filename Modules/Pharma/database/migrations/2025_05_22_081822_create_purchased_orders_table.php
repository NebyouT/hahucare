<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchased_orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('medicine_id');
            $table->bigInteger('pharma_id');
            $table->string('quantity');
            $table->string('delivery_date');
            $table->string('payment_status')->default('pending');
            $table->string('total_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_orders');
    }
};
