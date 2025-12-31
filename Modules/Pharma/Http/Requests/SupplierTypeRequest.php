<?php

namespace Modules\Pharma\Http\Requests;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Foundation\Http\FormRequest;

class SupplierTypeRequest extends FormRequest
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
        $id = $this->id;
        if ($this->isMethod('put')) {
            return [
                'name' => 'required|max:255|unique:supplier_types,name,' . $id,
            ];
        }
        return [
            'name' => 'required|max:255|unique:supplier_types,name',
        ];
    }
}
