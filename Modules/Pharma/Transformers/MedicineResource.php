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

class MedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $category = MedicineCategory::where('id', $this->category_id)->first();
        $form = MedicineForm::where('id', $this->form_id)->first();
        $manufacturer = Manufacturer::where('id', $this->manufacturer_id)->first();
        $supplier = Supplier::where('id', $this->supplier_id)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'dosage' => $this->dosage,
            'category' =>  new CategoryResource($category),
            'form' => new MedicineFormResource($form),
            'expiry_date' => $this->expiry_date,
            'note' => $this->note,
            'supplier' => new SupplierResource($supplier),
            'contact_number' => $this->contact_number,
            'payment_terms' => $this->payment_terms,
            'quntity' => $this->quntity,
            're_order_level' => $this->re_order_level,
            'manufacturer' => new ManufacturerResource($manufacturer),
            'batch_no' => $this->batch_no,
            'start_serial_no' => $this->start_serial_no,
            'end_serial_no' => $this->end_serial_no,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'stock_value' => $this->stock_value,
            'is_inclusive_tax' => $this->is_inclusive_tax,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
