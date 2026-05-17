<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class DashboardComment extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    protected $fillable = [
        'user_id',
        'clinic_id',
        'comment',
        'commentable_type',
        'commentable_id',
        'is_approved',
        'deleted_by',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinic()
    {
        return $this->belongsTo(\Modules\Clinic\Models\Clinics::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
