<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lab_equipment';

    protected $fillable = [
        'equipment_name',
        'equipment_code',
        'description',
        'manufacturer',
        'model_number',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'last_maintenance_date',
        'next_maintenance_date',
        'status',
        'location',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->whereDate('next_maintenance_date', '<=', now()->addDays(30));
    }
}
