<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Clinic\Models\Clinics;

class LabOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'clinic_id',
        'lab_id',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'notes',
        'total_amount',
        'discount_amount',
        'final_amount',
        'status',
        'order_date',
        'confirmed_date',
        'completed_date',
        'collection_type',
        'sample_collection_date',
        'collection_notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'confirmed_date' => 'datetime',
        'completed_date' => 'datetime',
        'sample_collection_date' => 'datetime',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinics::class, 'clinic_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function labOrderItems()
    {
        return $this->hasMany(LabOrderItem::class, 'lab_order_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function generateOrderNumber()
    {
        $prefix = 'LAB';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
