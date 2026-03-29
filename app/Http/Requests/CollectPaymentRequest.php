<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Http/Requests/CollectPaymentRequest.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:CASH,HEF,NSSF,CARD,BAKONG',
            'reference'    => 'nullable|string|max:80',
            'note'         => 'nullable|string|max:500',
            'collected_by' => 'nullable|string|max:120',
        ];
    }
}
