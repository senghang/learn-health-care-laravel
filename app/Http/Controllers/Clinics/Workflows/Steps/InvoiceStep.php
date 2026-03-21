<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Common\Constants\DateFormats;
use App\Common\Utils\CodeGenerator;
use App\Models\InvoiceModel;
use App\Models\InvoiceServiceModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceStep
 *
 * Blade field names → DB column names:
 *   payment_type              → invoices.payment_type      (HEF|NSSF|CASH)
 *   invoice_date              → invoices.invoice_date      (date)
 *   cashier                   → invoices.cashier
 *   total                     → invoices.total             (declared total)
 *   services[N][name]         → invoice_services.service_name
 *   services[N][category]     → invoice_services.service_category
 *   services[N][price]        → invoice_services.price  AND .payment
 *   services[N][paid_status]  → 1|0  →  .paid = price if 1, else 0
 *
 * Save strategy: updateOrCreate invoice header keyed on visit_code,
 * then forceDelete old service rows and re-insert fresh ones.
 *
 * paid column (decimal) = amount actually paid:
 *   if paid_status = 1  → paid = price  (fully paid)
 *   if paid_status = 0  → paid = 0      (pending)
 *
 * If _complete=1 is submitted, marks the visit as discharged.
 */
class InvoiceStep extends AbstractWorkflowStep
{
    public function id(): string          { return 'invoice'; }
    public function labelKm(): string     { return 'វិក្កយបត្រ'; }
    public function labelEn(): string     { return 'Invoice'; }
    public function icon(): string        { return '🧾'; }
    public function color(): string       { return '#00bcd4'; }
    public function description(): string { return 'HEF / NSSF / CASH — សេវា & ថ្នាំ'; }
    public function skippable(): bool     { return true; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'payment_type'              => 'required|in:HEF,NSSF,CASH',
            'invoice_date'              => 'nullable|date',
            'cashier'                   => 'nullable|string|max:120',
            'total'                     => 'nullable|numeric|min:0',
            'services'                  => 'nullable|array',
            'services.*.name'           => 'required_with:services|string|max:200',
            'services.*.category'       => 'nullable|string|max:80',
            'services.*.price'          => 'nullable|numeric|min:0',
            // paid_status: 1 = fully paid, 0 = pending
            'services.*.paid_status'    => 'nullable|in:0,1',
        ]);

        DB::transaction(function () use ($visit, $data, $request) {

            // ── 1. Upsert invoice header (one per visit) ──────────────────────
            $inv = InvoiceModel::updateOrCreate(
                ['visit_code' => $visit->code],
                [
                    'code'         => CodeGenerator::invoice($visit->code),
                    'patient_code' => $visit->patient_code,
                    'payment_type' => $data['payment_type'],
                    'invoice_date' => $data['invoice_date']
                        ? \Carbon\Carbon::parse($data['invoice_date'])->format(DateFormats::INPUT_DATE)
                        : today()->toDateString(),
                    'cashier'      => $data['cashier'] ?? null,
                    'total'        => $data['total']   ?? 0,
                ]
            );

            // ── 2. Replace all service rows cleanly ───────────────────────────
            InvoiceServiceModel::where('invoice_code', $inv->code)->forceDelete();

            // ── 3. Insert fresh service rows ──────────────────────────────────
            foreach ($data['services'] ?? [] as $svc) {
                if (empty(trim($svc['name'] ?? ''))) continue;

                $price      = (float) ($svc['price']       ?? 0);
                $paidStatus = (int)   ($svc['paid_status']  ?? 0);

                InvoiceServiceModel::create([
                    'invoice_code'     => $inv->code,
                    'service_name'     => $svc['name'],
                    'service_category' => $svc['category'] ?? null,
                    'price'            => $price,
                    // payment = amount charged to patient (same as price here)
                    'payment'          => $price,
                    // paid = amount actually received: full price if paid, 0 if pending
                    'paid'             => $paidStatus === 1 ? $price : 0,
                ]);
            }

            // ── 4. Recalculate total from line items ──────────────────────────
            $inv->load('services');
            $inv->recalculateTotal();

            // ── 5. Complete visit (discharge) if requested ────────────────────
            if ($request->input('_complete') == '1') {
                $visit->update([
                    'discharged_at' => now(),
                    'visit_outcome' => $visit->visit_outcome ?? 'Improved',
                ]);
            }
        });
    }

    public function viewData(VisitModel $visit): array
    {
        $invoice = InvoiceModel::where('visit_code', $visit->code)
            ->with('services', 'medications')
            ->latest()
            ->first();

        return [
            'invoice'  => $invoice,
            'services' => $invoice?->services  ?? collect([]),
            'meds'     => $invoice?->medications ?? collect([]),
        ];
    }
}
