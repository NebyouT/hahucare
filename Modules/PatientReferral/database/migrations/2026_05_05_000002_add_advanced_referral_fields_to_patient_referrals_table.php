<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_referrals', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->after('id')->unique();
            $table->enum('referral_type', ['quick', 'advanced'])->default('quick')->after('referral_code');
            $table->integer('patient_age')->nullable()->after('referral_type');
            $table->string('patient_sex')->nullable()->after('patient_age');
            $table->text('patient_address')->nullable()->after('patient_sex');
            $table->string('referring_faculty')->nullable()->after('patient_address');
            $table->string('receiving_faculty')->nullable()->after('referring_faculty');
            $table->text('chief_complaint')->nullable()->after('receiving_faculty');
            $table->text('history_findings')->nullable()->after('chief_complaint');
            $table->text('diagnosis')->nullable()->after('history_findings');
            $table->text('treatment_given')->nullable()->after('diagnosis');
            $table->text('investigation_done')->nullable()->after('treatment_given');
            $table->string('referring_clinic_name')->nullable()->after('investigation_done');
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
                'signature_data'
            ]);
        });
    }
};
