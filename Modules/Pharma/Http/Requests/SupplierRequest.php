<?php

namespace Modules\Pharma\Http\Requests;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
        $id = $this->route('supplier');
        if ($this->isMethod('put')) {
            return [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'supplier_type' => 'required|exists:supplier_types,id',
                'email' => 'required|email|max:255|unique:suppliers,email,' . $id,
                'contact_number' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[0-9\-\+\s\(\)]+$/',
                    'unique:suppliers,contact_number,' . $id,
                ],
                'payment_terms' => 'required|max:255',
            ];
        }
        return [
            'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'supplier_type' => 'required|exists:supplier_types,id',
                'email' => 'required|email|max:255|unique:suppliers,email,',
                'contact_number' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[0-9\-\+\s\(\)]+$/',
                    'unique:suppliers,contact_number',
                ],
                'payment_terms' => 'required|max:255',
        ];
    }
}
