<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePatientRequest — validation for editing an existing patient.
 *
 * Same fields as StorePatientRequest but code is immutable (not accepted).
 * Used by PatientController::update().
 */
class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Patient demographics ──────────────────────────────────────────
            'surname'        => 'required|string|max:120',
            'name'           => 'required|string|max:120',
            'sex'            => 'required|in:M,F',
            'birthdate'      => 'nullable|date|before_or_equal:today',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
            'occupation'     => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            'spid'           => 'nullable|string|max:30',
            'blood_type'     => 'nullable|string|max:10',
            'status'         => 'nullable|in:Active,Inactive',

            // ── Address ───────────────────────────────────────────────────────
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',

            // ── Identifications ───────────────────────────────────────────────
            'identifications'              => 'nullable|array|max:5',
            'identifications.*.id'         => 'nullable|integer',
            'identifications.*.card_type'  => 'required_with:identifications|string|max:60',
            'identifications.*.card_code'  => 'required_with:identifications|string|max:60',

            // ── Contacts ──────────────────────────────────────────────────────
            'contacts'                     => 'nullable|array|max:5',
            'contacts.*.id'                => 'nullable|integer',
            'contacts.*.contact_name'      => 'required_with:contacts|string|max:120',
            'contacts.*.contact_phone'     => 'nullable|string|max:30',
            'contacts.*.relationship'      => 'nullable|string|max:60',
            'contacts.*.is_emergency'      => 'nullable|boolean',
        ];
    }

    public function patientData(): array
    {
        return $this->only([
            'surname', 'name', 'sex', 'birthdate', 'phone',
            'nationality', 'occupation', 'marital_status', 'spid',
            'blood_type', 'status',
        ]);
    }

    public function addressData(): array
    {
        return $this->only([
            'province_name', 'district_name', 'commune_name',
            'village_name', 'house_number', 'street_number',
        ]);
    }

    public function identificationData(): array
    {
        return $this->input('identifications', []);
    }

    public function contactData(): array
    {
        return $this->input('contacts', []);
    }
}
