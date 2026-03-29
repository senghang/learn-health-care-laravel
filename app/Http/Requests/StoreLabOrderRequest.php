<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_code' => 'required|string|max:30|exists:patients,code',
            'visit_code'   => 'required|string|max:30|exists:visits,code',
            'title'        => 'nullable|string|max:200',
            'urgency'      => 'nullable|in:normal,urgent,stat',
            'requested_by' => 'nullable|string|max:120',
            'tests'        => 'required|array|min:1',
            'tests.*.name' => 'required|string|max:120',
            'tests.*.category'    => 'nullable|string|max:60',
            'tests.*.sample_type' => 'nullable|string|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'tests.required'       => 'At least one test must be selected.',
            'tests.*.name.required' => 'Each test must have a name.',
        ];
    }
}
