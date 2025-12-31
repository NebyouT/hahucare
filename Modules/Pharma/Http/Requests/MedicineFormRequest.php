<?php

namespace Modules\Pharma\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class MedicineFormRequest extends FormRequest
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
        $medicineFormId = $this->route('medicine_form') ?? $this->route('id');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('medicine_forms', 'name')->ignore($medicineFormId),
            ],
            // ...other rules...
        ];
    }
}
