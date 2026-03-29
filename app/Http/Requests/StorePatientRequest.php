<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Http/Requests/StorePatientRequest.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'surname'        => 'required|string|max:120',
            'name'           => 'required|string|max:120',
            'sex'            => 'required|in:M,F',
            'birthdate'      => 'nullable|date|before:today',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
            'occupation'     => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            'spid'           => 'nullable|string|max:30',
            'blood_type'     => 'nullable|string|max:10',
            'emergency_contact_name'  => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:30',
            // Address
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'surname.required' => __('validation.required', ['attribute' => __('app.patient_surname')]),
            'name.required'    => __('validation.required', ['attribute' => __('app.patient_name')]),
            'sex.required'     => __('validation.required', ['attribute' => __('app.patient_sex')]),
        ];
    }

    /**
     * Split validated data into patient data and address data.
     */
    public function patientData(): array
    {
        return $this->only([
            'surname', 'name', 'sex', 'birthdate', 'phone',
            'nationality', 'occupation', 'marital_status', 'spid',
            'blood_type', 'emergency_contact_name', 'emergency_contact_phone',
        ]);
    }

    public function addressData(): array
    {
        return $this->only([
            'province_name', 'district_name', 'commune_name',
            'village_name', 'house_number', 'street_number',
        ]);
    }
}
