<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'prescribed_at'         => 'nullable|date',
            'prescribed_by'         => 'required|string|max:120',
            'dispensed_status'      => 'nullable|in:,partial,dispensed',
            'dispensed_by'          => 'nullable|string|max:120',

            'meds'                  => 'required|array|min:1',
            'meds.*.medicine_name'  => 'required|string|max:200',
            'meds.*.medicine_code'  => 'nullable|string|max:30',
            'meds.*.strength'       => 'nullable|string|max:60',
            'meds.*.form'           => 'nullable|string|max:60',
            'meds.*.method'         => 'nullable|string|max:80',
            'meds.*.unit'           => 'nullable|string|max:40',
            'meds.*.morning'        => 'nullable|numeric|min:0',
            'meds.*.afternoon'      => 'nullable|numeric|min:0',
            'meds.*.evening'        => 'nullable|numeric|min:0',
            'meds.*.night'          => 'nullable|numeric|min:0',
            'meds.*.days'           => 'nullable|integer|min:1',
            'meds.*.interval'       => 'nullable|string|max:40',
            'meds.*.note'           => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'prescribed_by.required'            => 'Prescribing doctor name is required.',
            'meds.required'                     => 'At least one medication is required.',
            'meds.min'                          => 'At least one medication is required.',
            'meds.*.medicine_name.required'     => 'Each medication must have a name.',
        ];
    }
}
