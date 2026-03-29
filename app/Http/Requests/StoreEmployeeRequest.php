<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Http/Requests/StoreEmployeeRequest.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'surname'        => 'required|string|max:80',
            'name'           => 'required|string|max:80',
            'name_kh'        => 'nullable|string|max:80',
            'gender'         => 'nullable|in:M,F',
            'birthdate'      => 'nullable|date|before:today',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:120',
            'employee_type'  => 'required|string|max:40',
            'department_id'  => 'nullable|integer|exists:departments,id',
            'specialization' => 'nullable|string|max:120',
            'license_number' => 'nullable|string|max:60',
            'hire_date'      => 'nullable|date',
            'status'         => 'nullable|in:active,inactive,on_leave,terminated',
        ];
    }
}
