<?php

namespace Modules\Pharma\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Pharma\Models\SupplierType;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $supplierType = SupplierType::find($this->supplier_type_id);
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'supplier_type' => new SupplierTypeResource($supplierType),
            'pharma_id' => $this->pharma_id,
            'payment_terms' => $this->payment_terms,
            'image_url' => $this->getFirstMediaUrl('supplier_image'),
            'status' => (int)$this->status,

        ];
    }
}
