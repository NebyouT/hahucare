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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dosage')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->bigInteger('form_id')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->text('note')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('quntity')->nullable();
            $table->string('re_order_level')->nullable();
            $table->bigInteger('manufacturer_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('start_serial_no')->nullable();
            $table->string('end_serial_no')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('selling_price')->nullable();
            $table->tinyInteger('is_inclusive_tax')->nullable();
            $table->string('stock_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
