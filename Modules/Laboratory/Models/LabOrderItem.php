<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'lab_service_id',
        'service_name',
        'service_description',
        'price',
        'discount_amount',
        'final_price',
        'status',
        'lab_result_id',
        'urgent_flag',
        'clinical_notes',
        'sample_type',
        'fasting_required',
        'special_instructions',
        'result_file',
        'technician_note',
        'result_uploaded_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'urgent_flag' => 'boolean',
        'fasting_required' => 'boolean',
        'result_uploaded_at' => 'datetime',
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function labService()
    {
        return $this->belongsTo(LabService::class, 'lab_service_id');
    }

    public function labResult()
    {
        return $this->belongsTo(LabResult::class, 'lab_result_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUrgent($query)
    {
        return $query->where('urgent_flag', true);
    }
}
