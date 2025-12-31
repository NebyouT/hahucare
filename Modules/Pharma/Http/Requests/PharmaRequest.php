<?php

namespace Modules\Pharma\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PharmaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get user ID for update - check multiple possible route parameter names
        // For update: route is 'update-pharma/{id}', so $this->route('id') will return the id
        // For create: route is 'add-pharma', so $this->route('id') will return null
        $userId = $this->route('id') ?? $this->route('pharma') ?? null;
        
        // Determine if this is an update request
        $isUpdate = !empty($userId);
        
        return [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email' . ($isUpdate ? ",$userId" : ''),
            'contact_number' => 'required|string|regex:/^[0-9\-\+\s\(\)]+$/|unique:users,mobile' . ($isUpdate ? ",$userId" : ''),
            // Password required only if create, optional if update
            // The 'confirmed' rule automatically validates password_confirmation field
            'password'       => $isUpdate ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'dob'            => 'required|date|before:today',
            'clinic'         => 'required|string',
            'gender'         => 'required|in:male,female,other',
            'address'        => 'nullable|string|max:255',
            'pharma_commission' => 'required',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     * 
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ];

        // Check if this is an API request
        $isApiRequest = $this->expectsJson() 
            || $this->wantsJson()
            || $this->is('api/*')
            || str_starts_with($this->path(), 'api/')
            || $this->routeIs('api.*')
            || $this->header('Accept') === 'application/json'
            || $this->header('Content-Type') === 'application/json';

        if ($isApiRequest) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        // For web requests, use default Laravel behavior
        parent::failedValidation($validator);
    }
}
