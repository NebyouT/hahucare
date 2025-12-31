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
        Schema::create('medicine_history', function (Blueprint $table) {
            $table->id();
        
            // Foreign key to medicines table
            $table->unsignedBigInteger('medicine_id');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
        
            // Optional: track which fields changed
            $table->string('batch_no')->nullable();
            $table->string('quntity')->nullable();
            $table->string('start_serial_no')->nullable();
            $table->string('end_serial_no')->nullable();
            $table->string('stock_value')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_history');
    }
};
