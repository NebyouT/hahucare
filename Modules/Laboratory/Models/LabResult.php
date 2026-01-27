<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class LabResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'result_code',
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'appointment_id',
        'test_date',
        'result_date',
        'result_value',
        'remarks',
        'status',
        'technician_id',
        'sample_type',
        'sample_id',
        'attachments',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'test_date' => 'datetime',
        'result_date' => 'datetime',
        'attachments' => 'array',
    ];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
