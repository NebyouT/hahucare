<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pharma\Database\factories\MedicineHistoryFactory;
use Modules\Pharma\Models\Medicine;

class MedicineHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'medicine_history';
    protected $fillable = ['medicine_id','batch_no','quntity','start_serial_no','end_serial_no','stock_value'];
    
    protected $casts = [
        'quntity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected static function newFactory(): MedicineHistoryFactory
    {
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'id');
    }
}
