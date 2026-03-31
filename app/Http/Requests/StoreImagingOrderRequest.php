<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreImagingOrderRequest — validates standalone imaging order creation.
 * Used by ImageryController::store().
 */
class StoreImagingOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_code'   => 'required|string|exists:patients,code',
            'visit_code'     => 'required|string|exists:visits,code',
            'category'       => 'required|string|max:80',
            'title'          => 'nullable|string|max:200',
            'urgency'        => 'nullable|in:normal,urgent,stat',
            'requested_by'   => 'nullable|string|max:120',
        ];
    }
}
