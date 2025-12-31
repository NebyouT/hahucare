<?php

namespace Modules\Pharma\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Pharma\Models\Medicine;
use Carbon\Carbon;
class MedicineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('medicine_category');
       
        return [
            'name'                  => 'required|max:255',
            'dosage'                => 'required|string|max:100',
            'medicine_category_id'  => 'required|string|max:100',
            'form_id'               => 'required|string|max:100',
            'expiry_date'           => 'required|date',
            'supplier_id'           => 'required|string|max:255',
            'contact_number'        => 'required',
            'payment_terms'        => 'required|string',
            'quntity'             => 'required|integer|min:1',
            're_order_level'        => 'required|integer|min:0',
            'manufacturer'          => 'required|string|max:255',
            'batch_no'              => 'required|string|max:100',
            'start_serial_no'       => 'nullable|integer|min:0',
            'end_serial_no'         => 'nullable|integer',
            'purchase_price'        => 'required|numeric|min:0',
            'selling_price'         => 'required|numeric|min:0',
            'tax'                   => 'nullable|numeric|min:0|max:100',
            'is_inclusive_tax'      => 'nullable|boolean',
            'stock_value'           => 'nullable|numeric',
            'note'                  => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $name = $this->name;
            $dosage = $this->dosage;
            $pharmaId = auth()->id();

            $exists = Medicine::where('name', $name)
                ->where('dosage', $dosage)
                ->where('expiry_date', '>=', Carbon::today())
                ->where('pharma_id', $pharmaId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', __('pharma::validation.medicine_duplicate_name'));
                $validator->errors()->add('dosage', __('pharma::validation.medicine_duplicate_dosage'));
            }
        });
    }
}
