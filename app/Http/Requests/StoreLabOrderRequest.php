<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreLabOrderRequest — validates standalone lab order creation.
 * Used by LaboratoryController::store().
 */
class StoreLabOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_code'       => 'required|string|exists:patients,code',
            'visit_code'         => 'required|string|exists:visits,code',
            'encounter_code'     => 'nullable|string',
            'category'           => 'nullable|string|max:80',
            'title'              => 'nullable|string|max:200',
            'urgency'            => 'nullable|in:normal,urgent,stat',
            'requested_by'       => 'nullable|string|max:120',

            // Test items (at least one required)
            'tests'              => 'required|array|min:1',
            'tests.*.name'       => 'required|string|max:200',
            'tests.*.category'   => 'nullable|string|max:80',
            'tests.*.value_type' => 'nullable|string|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'tests.required'       => 'At least one lab test is required.',
            'tests.*.name.required' => 'Each test must have a name.',
        ];
    }
}
