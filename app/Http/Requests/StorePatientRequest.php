<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePatientRequest — validation for creating a new patient.
 *
 * Separates validation from controller (Laravel best practice).
 * Used by PatientController::store().
 */
class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/permission
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

            // ── Address ───────────────────────────────────────────────────────
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',

            // ── Identifications (array of cards) ──────────────────────────────
            'identifications'              => 'nullable|array|max:5',
            'identifications.*.card_type'  => 'required_with:identifications|string|max:60',
            'identifications.*.card_code'  => 'required_with:identifications|string|max:60',

            // ── Contacts (array) ──────────────────────────────────────────────
            'contacts'                     => 'nullable|array|max:5',
            'contacts.*.contact_name'      => 'required_with:contacts|string|max:120',
            'contacts.*.contact_phone'     => 'nullable|string|max:30',
            'contacts.*.relationship'      => 'nullable|string|max:60',
            'contacts.*.is_emergency'      => 'nullable|boolean',
        ];
    }

    /**
     * Extract only patient demographic fields.
     */
    public function patientData(): array
    {
        return $this->only([
            'surname', 'name', 'sex', 'birthdate', 'phone',
            'nationality', 'occupation', 'marital_status', 'spid', 'blood_type',
        ]);
    }

    /**
     * Extract only address fields.
     */
    public function addressData(): array
    {
        return $this->only([
            'province_name', 'district_name', 'commune_name',
            'village_name', 'house_number', 'street_number',
        ]);
    }

    /**
     * Extract identification card rows.
     */
    public function identificationData(): array
    {
        return $this->input('identifications', []);
    }

    /**
     * Extract contact rows.
     */
    public function contactData(): array
    {
        return $this->input('contacts', []);
    }
}
