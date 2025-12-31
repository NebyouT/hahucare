<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pharma\Database\factories\SupplierTypeFactory;

class SupplierType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'status'];
    
    protected static function newFactory(): SupplierTypeFactory
    {
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
}
