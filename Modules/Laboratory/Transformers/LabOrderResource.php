<?php

namespace Modules\Laboratory\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LabOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'lab_name' => optional($this->lab)->name,
            'lab_id' => $this->lab_id,
            'clinic_name' => optional($this->clinic)->name,
            'doctor_name' => optional($this->doctor)->first_name . ' ' . optional($this->doctor)->last_name,
            'encounter_id' => $this->encounter_id,
            'order_date' => $this->order_date ? Carbon::parse($this->order_date)->format('Y-m-d H:i:s') : null,
            'order_type' => $this->order_type,
            'priority' => $this->priority,
            'notes' => $this->notes,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,
            'status' => $this->status,
            'completed_date' => $this->completed_date ? Carbon::parse($this->completed_date)->format('Y-m-d H:i:s') : null,
            'items' => LabOrderItemResource::collection($this->whenLoaded('labOrderItems')),
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
