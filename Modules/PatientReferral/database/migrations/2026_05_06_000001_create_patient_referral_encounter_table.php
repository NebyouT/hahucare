<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_referral_encounter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained('patient_referrals')->onDelete('cascade');
            $table->foreignId('encounter_id')->constrained('patient_encounters')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['referral_id', 'encounter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_referral_encounter');
    }
};
