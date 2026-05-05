<?php

namespace Modules\PatientReferral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientReferral extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'referred_by',
        'referred_to',
        'reason',
        'notes',
        'status',
        'referral_date',
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
    ];

    protected $casts = [
        'referral_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'encounter_ids' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Models\User::class, 'patient_id');
    }

    public function referredByDoctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'referred_by');
    }

    public function referredToDoctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'referred_to');
    }

    public function encounters()
    {
        return $this->belongsToMany(\Modules\Appointment\Models\PatientEncounter::class, 'patient_referral_encounter', 'referral_id', 'encounter_id');
    }
}