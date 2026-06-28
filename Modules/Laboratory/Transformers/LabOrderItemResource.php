<?php

namespace Modules\Laboratory\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LabOrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        $files = [];
        if ($this->result_file) {
            $paths = explode(',', $this->result_file);
            foreach ($paths as $path) {
                $path = trim($path);
                if ($path) {
                    $files[] = [
                        'url' => asset('storage/' . $path),
                        'name' => basename($path),
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'lab_order_id' => $this->lab_order_id,
            'service_name' => $this->service_name,
            'service_description' => $this->service_description,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'status' => $this->status,
            'urgent_flag' => $this->urgent_flag,
            'clinical_notes' => $this->clinical_notes,
            'sample_type' => $this->sample_type,
            'fasting_required' => $this->fasting_required,
            'special_instructions' => $this->special_instructions,
            'result_file' => $this->result_file,
            'result_file_urls' => $files,
            'result_value' => $this->labResult ? $this->labResult->result_value : null,
            'result_notes' => $this->labResult ? $this->labResult->remarks : null,
            'technician_note' => $this->technician_note,
            'result_uploaded_at' => $this->result_uploaded_at ? Carbon::parse($this->result_uploaded_at)->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
