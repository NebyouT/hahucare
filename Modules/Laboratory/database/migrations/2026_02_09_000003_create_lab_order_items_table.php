<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_order_id');
            $table->unsignedBigInteger('lab_test_id');
            $table->string('test_name');
            $table->text('test_description')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            
            // Status
            $table->string('status')->default('pending'); // pending, in_progress, completed
            
            // Result Reference
            $table->unsignedBigInteger('lab_result_id')->nullable();
            
            $table->timestamps();
            
            // Foreign keys will be added after confirming table names
            // $table->foreign('lab_order_id')->references('id')->on('lab_orders')->onDelete('cascade');
            // $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade');
            // $table->foreign('lab_result_id')->references('id')->on('lab_results')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
    }
};
