<?php

namespace Modules\Pharma\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Prescription extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'medicine_id' => 'required|exists:medicines,id',
            'frequency'   => 'required|string|max:255',
            'duration'    => 'required|string|max:255',
            'quantity'    => 'required|integer|min:1',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
