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
    ];

    protected $casts = [
        'referral_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}