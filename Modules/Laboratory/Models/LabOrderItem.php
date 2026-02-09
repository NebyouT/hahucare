<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'test_name',
        'test_description',
        'price',
        'discount_amount',
        'final_price',
        'status',
        'lab_result_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
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
}
