<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('lab_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('encounter_id')->nullable();
            
            // Order Details
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            
            // Status
            $table->string('status')->default('pending'); // pending, confirmed, in_progress, completed, cancelled
            $table->dateTime('order_date');
            $table->dateTime('confirmed_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            
            // Sample Collection
            $table->string('collection_type')->default('clinic'); // clinic, home
            $table->dateTime('sample_collection_date')->nullable();
            $table->text('collection_notes')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys will be added after confirming table names
            // $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            // $table->foreign('lab_id')->references('id')->on('labs')->onDelete('cascade');
            // $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('encounter_id')->references('id')->on('patient_encounters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
