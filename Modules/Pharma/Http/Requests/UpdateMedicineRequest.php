<?php
namespace Modules\Pharma\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'dosage' => 'required|string|max:100',
            'medicine_category_id' => 'required|string|max:100',
            'form_id' => 'required|string|max:100',
            'expiry_date' => 'required|date',
            'supplier_id' => 'required|string|max:255',
            'contact_number' => 'required',
            'payment_terms' => 'required|string',
            'quntity' => 'required|integer|min:1',
            're_order_level' => 'required|integer|min:0',
            'manufacturer' => 'required|string|max:255',
            'batch_no' => 'required|string|max:100',
            'start_serial_no' => 'required|integer|min:0',
            'end_serial_no' => 'required|integer',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
