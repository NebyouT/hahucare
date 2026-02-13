<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_code',
        'test_name',
        'description',
        'category_id',
        'lab_id',
        'price',
        'discount_price',
        'discount_type',
        'duration_minutes',
        'preparation_instructions',
        'normal_range',
        'unit_of_measurement',
        'sample_type',
        'reporting_time',
        'is_active',
        'is_featured',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(LabTestCategory::class, 'category_id');
    }

    public function results()
    {
        return $this->hasMany(LabResult::class, 'lab_test_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFinalPriceAttribute()
    {
        if ($this->discount_price && $this->discount_price < $this->price) {
            return $this->discount_price;
        }
        return $this->price;
    }
}
