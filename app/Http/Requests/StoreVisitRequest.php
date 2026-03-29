<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Http/Requests/StoreVisitRequest.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_code'   => 'required|string|max:30|exists:patients,code',
            'surname'        => 'required|string|max:120',
            'given_name'     => 'required|string|max:120',
            'visit_type'     => 'required|in:OPD,IPD',
            'admission_type' => 'nullable|string|max:80',
            'admitted_at'    => 'nullable|date',
            'gender'         => 'nullable|in:M,F',
            'birthdate'      => 'nullable|date',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
        ];
    }
}
