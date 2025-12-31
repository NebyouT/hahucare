<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pharma\Database\factories\MedicineCategoryFactory;

class MedicineCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'status'];
    
    protected static function newFactory(): MedicineCategoryFactory
    {
    }
    public function medicine()
    {
        return $this->hasMany(Medicine::class);
    }
}
