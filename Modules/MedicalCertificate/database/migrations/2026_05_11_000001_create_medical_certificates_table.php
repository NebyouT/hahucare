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
        Schema::create('medical_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('doctor_id')->nullable();
            $table->foreignId('encounter_id')->nullable();
            $table->foreignId('clinic_id')->nullable();
            
            $table->string('certificate_number')->unique();
            $table->string('certificate_type')->default('medical_leave'); // medical_leave, fitness, recovery, other
            $table->date('issue_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days')->default(0);
            
            $table->text('diagnosis')->nullable();
            $table->text('reason')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('notes')->nullable();
            
            $table->string('status')->default('issued'); // issued, printed, cancelled
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();
            
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_certificates');
    }
};
