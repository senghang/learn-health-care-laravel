<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\InvoiceMedicationModel;
use App\Models\InvoiceModel;
use App\Models\InvoiceServiceModel;
use App\Models\MedicineModel;
use App\Models\ServiceModel;
use App\Models\VisitModel;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'payment_type' => 'required|in:HEF,NSSF,CASH,CARD',
            'cashier' => 'nullable|string|max:120',
            'invoice_date' => 'nullable|date',
            'total' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,partial,paid,void',
            'services' => 'nullable|array',
            'services.*.service_code' => 'nullable|string|max:30',
            'services.*.name' => 'required_with:services|string|max:200',
            'services.*.category' => 'nullable|string|max:80',
            'services.*.price' => 'nullable|numeric|min:0',
            'services.*.paid_status' => 'nullable|in:0,1',
            'inv_meds' => 'nullable|array',
            'inv_meds.*.name' => 'required_with:inv_meds|string|max:200',
            'inv_meds.*.qty' => 'nullable|numeric|min:0',
            'inv_meds.*.price' => 'nullable|numeric|min:0',
            'inv_meds.*.paid_status' => 'nullable|in:0,1',
        ]);

        // ── Out-of-stock validation BEFORE any DB write ─────────────────────
        $stockItems = collect($data['inv_meds'] ?? [])
            ->filter(fn($m) => !empty($m['medicine_id']) && !empty(trim($m['name'] ?? '')))
            ->map(fn($m) => [
                'medicine_id' => (int)$m['medicine_id'],
                'qty' => (int)($m['qty'] ?? 1),
                'name' => $m['name'],
            ])->all();

        $stockErrors = StockService::validate($stockItems);
        if (!empty($stockErrors)) {
            throw ValidationException::withMessages([
                'inv_meds' => $stockErrors,
            ]);
        }

        DB::transaction(function () use ($visit, $data, $stockItems) {
            $invCode = 'INV-' . $visit->code;

            // Force-delete any soft-deleted row to avoid unique constraint collision
            InvoiceModel::withTrashed()
                ->where('visit_code', $visit->code)
                ->whereNotNull('deleted_at')
                ->forceDelete();

            $isNewInvoice = !InvoiceModel::where('visit_code', $visit->code)->exists();

            $inv = InvoiceModel::updateOrCreate(
                ['visit_code' => $visit->code],
                [
                    'code' => $invCode,
                    'patient_code' => $visit->patient_code,
                    'payment_type' => $data['payment_type'],
                    'invoice_date' => $data['invoice_date'] ?? today(),
                    'cashier' => $data['cashier'] ?? auth()->user()?->name,
                    'status' => $data['status'] ?? 'pending',
                ]
            );

            // If re-saving: return old stock before wiping medications
            if (!$isNewInvoice) {
                StockService::returnStock($inv->code);
            }

            $inv->services()->forceDelete();
            $inv->medications()->forceDelete();

            // ── Services ───────────────────────────────────────────────────
            $svcTotal = 0;
            foreach ($data['services'] ?? [] as $svc) {
                if (empty(trim($svc['name'] ?? ''))) continue;
                $price = (float)($svc['price'] ?? 0);
                $paid = ($svc['paid_status'] ?? '0') === '1' ? $price : 0.0;
                $svcTotal += $price;
                InvoiceServiceModel::create([
                    'invoice_code' => $inv->code,
                    'service_code' => $svc['service_code'] ?? null,
                    'service_name' => $svc['name'],
                    'service_category' => $svc['category'] ?? null,
                    'price' => $price,
                    'payment' => $price,
                    'paid' => $paid,
                ]);
            }

            // ── Medications (with medicine_id for stock audit) ─────────────
            $medTotal = 0;
            $dispenseItems = [];
            foreach ($data['inv_meds'] ?? [] as $med) {
                if (empty(trim($med['name'] ?? ''))) continue;
                $price = (float)($med['price'] ?? 0);
                $qty = (float)($med['qty'] ?? 1);
                $paid = ($med['paid_status'] ?? '0') === '1' ? $price * $qty : 0.0;
                $medTotal += $price * $qty;

                InvoiceMedicationModel::create([
                    'invoice_code' => $inv->code,
                    'medicine_id' => $med['medicine_id'] ?? null,
                    'medicine_code' => $med['medicine_code'] ?? null,
                    'medicine_name' => $med['name'],
                    'quantity' => $qty,
                    'price' => $price,
                    'payment' => $price * $qty,
                    'paid' => $paid,
                ]);

                if (!empty($med['medicine_id'])) {
                    $dispenseItems[] = [
                        'medicine_id' => (int)$med['medicine_id'],
                        'qty' => (int)$qty,
                        'unit_price' => $price,
                        'name' => $med['name'],
                    ];
                }
            }

            // ── Deduct stock ──────────────────────────────────────────────
            if (!empty($dispenseItems)) {
                StockService::dispense(
                    $dispenseItems,
                    $inv->code,
                    $visit->code,
                    $visit->patient_code
                );
            }

            // Final total
            $declared = (float)($data['total'] ?? 0);
            $computed = $svcTotal + $medTotal;
            $inv->update(['total' => $declared > 0 ? $declared : $computed]);
        });
    }

    public function viewData(VisitModel $visit): array
    {
        $invoice = InvoiceModel::where('visit_code', $visit->code)
            ->with('services', 'medications')->latest()->first();

        $svcCatalog = ServiceModel::where('clinic_id', currentClinic()->id)
            ->where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'category', 'price']);

        $medCatalog = MedicineModel::where('clinic_id', currentClinic()->id)
            ->where('is_active', true)
            ->orderBy('form')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'generic_name', 'form', 'strength', 'unit', 'price', 'stock', 'stock_alert']);

        return [
            'invoice' => $invoice,
            'services' => $invoice?->services ?? collect([]),
            'meds' => $invoice?->medications ?? collect([]),
            'svcCatalog' => $svcCatalog,
            'medCatalog' => $medCatalog,
        ];
    }
}
