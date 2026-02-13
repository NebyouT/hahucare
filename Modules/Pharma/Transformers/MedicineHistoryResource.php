<?php

namespace Modules\Pharma\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicineHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'medicine_id' => $this->medicine_id,
            'medicine_name' => optional($this->medicine)->name ?? '-',
            'batch_no' => $this->batch_no,
            'quantity' => $this->quntity,
            'start_serial_no' => $this->start_serial_no,
            'end_serial_no' => $this->end_serial_no,
            'stock_value' => $this->stock_value,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
