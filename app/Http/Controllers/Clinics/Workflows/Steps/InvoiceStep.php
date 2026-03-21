<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\InvoiceMedicationModel;
use App\Models\InvoiceModel;
use App\Models\InvoiceServiceModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class InvoiceStep extends AbstractWorkflowStep
{
    public function id(): string
    {
        return 'invoice';
    }

    public function labelKm(): string
    {
        return 'វិក្កយបត្រ';
    }

    public function labelEn(): string
    {
        return 'Invoice';
    }

    public function icon(): string
    {
        return '🧾';
    }

    public function color(): string
    {
        return '#00bcd4';
    }

    public function description(): string
    {
        return 'HEF / NSSF / CASH — សេវា & ថ្នាំ';
    }

    public function skippable(): bool
    {
        return true;
    } // Set false to force billing

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'payment_type' => 'required|in:HEF,NSSF,CASH',
            'cashier' => 'nullable|string|max:120',
            'services' => 'nullable|array',
            'services.*.service_name' => 'required_with:services|string',
            'services.*.price' => 'nullable|numeric|min:0',
            'services.*.payment' => 'nullable|numeric|min:0',
            'medications' => 'nullable|array',
            'medications.*.medicine_name' => 'required_with:medications|string',
            'medications.*.quantity' => 'nullable|numeric|min:0',
            'medications.*.price' => 'nullable|numeric|min:0',
        ]);

        $inv = InvoiceModel::create([
            'code' => 'INV-' . $visit->code . '-' . now()->timestamp,
            'patient_code' => $visit->patient_code,
            'visit_code' => $visit->code,
            'payment_type' => $data['payment_type'],
            'invoice_date' => today(),
            'cashier' => $data['cashier'] ?? null,
        ]);

        foreach ($data['services'] ?? [] as $svc) {
            InvoiceServiceModel::create(array_merge($svc, ['invoice_code' => $inv->code]));
        }

        foreach ($data['medications'] ?? [] as $med) {
            InvoiceMedicationModel::create(array_merge($med, ['invoice_code' => $inv->code]));
        }

        // Recalculate total from line items
        $inv->load('services', 'medications');
        $inv->recalculateTotal();
    }

    public function viewData(VisitModel $visit): array
    {
        return ['invoices' => $visit->invoices()->with('services', 'medications')->latest()->get()];
    }
}
