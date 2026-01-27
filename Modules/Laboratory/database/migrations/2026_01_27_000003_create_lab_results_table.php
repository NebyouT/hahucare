<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->string('result_code')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('lab_test_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->dateTime('test_date');
            $table->dateTime('result_date')->nullable();
            $table->text('result_value')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('sample_id')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
