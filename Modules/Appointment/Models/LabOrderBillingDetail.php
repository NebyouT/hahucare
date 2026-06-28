<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabOrderBillingDetail extends Model
{
    use HasFactory;

    protected $fillable = ['encounter_id', 'exclusive_tax', 'exclusive_tax_amount', 'total_amount'];

    protected $casts = [
        'exclusive_tax' => 'array',
        'exclusive_tax_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function encounter()
    {
        return $this->belongsTo(PatientEncounter::class, 'encounter_id', 'id');
    }
}
