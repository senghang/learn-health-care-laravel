<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_code'   => 'required|string|exists:patients,code',
            'visit_code'     => 'nullable|string|max:30',
            'payment_type'   => 'required|in:CASH,HEF,NSSF,CARD',
            'invoice_date'   => 'nullable|date',
            'due_date'       => 'nullable|date|after_or_equal:invoice_date',
            'cashier'        => 'nullable|string|max:120',
            'notes'          => 'nullable|string',
            'discount_total' => 'nullable|numeric|min:0',
            'tax_total'      => 'nullable|numeric|min:0',

            'services'                    => 'nullable|array',
            'services.*.service_code'     => 'nullable|string|max:30',
            'services.*.name'             => 'required_with:services|string|max:200',
            'services.*.category'         => 'nullable|string|max:80',
            'services.*.qty'              => 'nullable|numeric|min:0',
            'services.*.price'            => 'nullable|numeric|min:0',

            'inv_meds'                    => 'nullable|array',
            'inv_meds.*.medicine_id'      => 'nullable|integer',
            'inv_meds.*.medicine_code'    => 'nullable|string|max:30',
            'inv_meds.*.name'             => 'required_with:inv_meds|string|max:200',
            'inv_meds.*.qty'              => 'nullable|numeric|min:0',
            'inv_meds.*.price'            => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $hasSvc = collect($this->input('services', []))
                ->filter(fn($r) => !empty(trim($r['name'] ?? '')))->isNotEmpty();
            $hasMed = collect($this->input('inv_meds', []))
                ->filter(fn($r) => !empty(trim($r['name'] ?? '')))->isNotEmpty();

            if (!$hasSvc && !$hasMed) {
                $v->errors()->add('services', 'At least one service or medication line item is required.');
            }
        });
    }
}
