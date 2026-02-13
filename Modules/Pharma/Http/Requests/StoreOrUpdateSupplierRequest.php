<?php
namespace Modules\Pharma\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateSupplierRequest extends FormRequest
{
    public function rules()
    {
        $supplierId = $this->input('supplier_id');

        return [
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'nullable|email|unique:suppliers,email,' . $supplierId,
            'contact_number'   => 'nullable|string|max:20',
            'supplier_type_id' => 'nullable|exists:supplier_types,id',
            'pharma_id'        => 'nullable|integer',
            'payment_terms'    => 'nullable|string|max:255',
            'status'           => 'required|in:0,1',
            'image'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
