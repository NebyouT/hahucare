<?php

namespace Modules\Pharma\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Pharma\Models\Manufacturer;
use Modules\Pharma\Models\MedicineCategory;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Models\Supplier;
use Modules\Pharma\Transformers\CategoryResource;
use Modules\Pharma\Transformers\MedicineFormResource;
use Modules\Pharma\Transformers\ManufacturerResource;
use Modules\Pharma\Transformers\SupplierResource;

class MedicineCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
