<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_referrals', function (Blueprint $table) {
            // Add foreign key constraints only if they don't exist
            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'patient_referrals' AND CONSTRAINT_NAME LIKE '%_foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            if (!in_array('patient_referrals_patient_id_foreign', $foreignKeys)) {
                $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            }

            if (!in_array('patient_referrals_referred_by_foreign', $foreignKeys)) {
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('cascade');
            }

            if (!in_array('patient_referrals_referred_to_foreign', $foreignKeys)) {
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
