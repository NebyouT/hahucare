<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_referrals', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('patient_referrals', 'patient_id')) {
                $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_referrals', 'referred_by')) {
                $table->foreignId('referred_by')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_referrals', 'referred_to')) {
                $table->foreignId('referred_to')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('patient_referrals', 'reason')) {
                $table->string('reason');
            }
            if (!Schema::hasColumn('patient_referrals', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('patient_referrals', 'status')) {
                $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            }
            if (!Schema::hasColumn('patient_referrals', 'referral_date')) {
                $table->date('referral_date');
            }
            if (!Schema::hasColumn('patient_referrals', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_referrals');
    }
};