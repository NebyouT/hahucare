<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clinic\Models\Clinics;

class Lab extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'clinic_id',
        'lab_code',
        'phone_number',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'operating_hours',
        'time_slot_duration',
        'is_active',
        'is_featured',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'operating_hours' => 'array',
        'time_slot_duration' => 'integer',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinics::class, 'clinic_id');
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'lab_id');
    }

    public function labTests()
    {
        return $this->hasMany(LabTest::class, 'lab_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getOperatingHoursAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setOperatingHoursAttribute($value)
    {
        $this->attributes['operating_hours'] = is_array($value) ? json_encode($value) : $value;
    }
}
