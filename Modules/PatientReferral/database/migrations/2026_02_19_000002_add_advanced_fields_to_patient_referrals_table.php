<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_referrals', function (Blueprint $table) {
            // Add foreign key constraints if they don't exist
            if (!Schema::hasColumn('patient_referrals', 'patient_id')) {
                $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            } else {
                $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            }

            if (!Schema::hasColumn('patient_referrals', 'referred_by')) {
                $table->foreignId('referred_by')->constrained('users')->onDelete('cascade');
            } else {
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('cascade');
            }

            if (!Schema::hasColumn('patient_referrals', 'referred_to')) {
                $table->foreignId('referred_to')->constrained('users')->onDelete('cascade');
            } else {
                $table->foreign('referred_to')->references('id')->on('users')->onDelete('cascade');
            }

            // Add advanced referral fields
            $table->string('referral_code')->nullable()->after('id');
            $table->enum('referral_type', ['quick', 'advanced'])->default('quick')->after('referral_code');
            $table->integer('patient_age')->nullable()->after('referral_type');
            $table->string('patient_sex', 20)->nullable()->after('patient_age');
            $table->text('patient_address')->nullable()->after('patient_sex');
            $table->string('referring_faculty', 255)->nullable()->after('patient_address');
            $table->string('receiving_faculty', 255)->nullable()->after('referring_faculty');
            $table->text('chief_complaint')->nullable()->after('receiving_faculty');
            $table->text('history_findings')->nullable()->after('chief_complaint');
            $table->text('diagnosis')->nullable()->after('history_findings');
            $table->text('treatment_given')->nullable()->after('diagnosis');
            $table->text('investigation_done')->nullable()->after('treatment_given');
            $table->string('referring_clinic_name', 255)->nullable()->after('investigation_done');
            $table->text('contact_information')->nullable()->after('referring_clinic_name');
            $table->json('encounter_ids')->nullable()->after('contact_information');
            $table->text('signature_data')->nullable()->after('encounter_ids');
        });
    }

    public function down(): void
    {
        Schema::table('patient_referrals', function (Blueprint $table) {
            $table->dropColumn([
                'referral_code',
                'referral_type',
                'patient_age',
                'patient_sex',
                'patient_address',
                'referring_faculty',
                'receiving_faculty',
                'chief_complaint',
                'history_findings',
                'diagnosis',
                'treatment_given',
                'investigation_done',
                'referring_clinic_name',
                'contact_information',
                'encounter_ids',
                'signature_data',
            ]);
        });
    }
};
