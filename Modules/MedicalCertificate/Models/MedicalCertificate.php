<?php

namespace Modules\MedicalCertificate\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCertificate extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'medical_certificates';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'encounter_id',
        'clinic_id',
        'certificate_number',
        'certificate_type',
        'issue_date',
        'start_date',
        'end_date',
        'duration_days',
        'diagnosis',
        'reason',
        'recommendations',
        'notes',
        'status',
        'is_printed',
        'printed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Models\User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    public function encounter()
    {
        return $this->belongsTo(\Modules\Appointment\Models\PatientEncounter::class, 'encounter_id');
    }

    public function clinic()
    {
        return $this->belongsTo(\Modules\Clinic\Models\Clinics::class, 'clinic_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    public static function generateCertificateNumber()
    {
        $prefix = 'MC';
        $date = now()->format('Ymd');
        $lastCertificate = self::where('certificate_number', 'like', "{$prefix}{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate->certificate_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$date}{$newNumber}";
    }
}
