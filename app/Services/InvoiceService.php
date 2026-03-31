<?php

namespace App\Services;

use App\Models\InvoiceMedicationModel;
use App\Models\InvoiceModel;
use App\Models\InvoiceServiceModel;
use App\Models\PaymentModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceService — billing lifecycle.
 *
 * Create → Add line items → Collect payment → Reconcile status.
 * ALL mutating operations are transactional.
 */
class InvoiceService
{
    // ── Read ──────────────────────────────────────────────────────────────────

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return InvoiceModel::query()
            ->with(['patient', 'visit', 'payments'])
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('code', 'like', "%{$s}%")
                  ->orWhereHas('patient', fn($p) =>
                      $p->where('surname', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                  )
            )
            ->when($filters['status'] ?? null,       fn($q, $v) => $q->where('status', $v))
            ->when($filters['payment_type'] ?? null, fn($q, $v) => $q->where('payment_type', $v))
            ->when($filters['date'] ?? null,         fn($q, $v) => $q->whereDate('invoice_date', $v))
            ->latest('invoice_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByCode(string $code): InvoiceModel
    {
        return InvoiceModel::where('code', $code)
            ->with(['patient', 'visit', 'services', 'medications', 'payments'])
            ->firstOrFail();
    }

    public function stats(): array
    {
        return [
            'total'   => InvoiceModel::count(),
            'today'   => InvoiceModel::whereDate('invoice_date', today())->count(),
            'pending' => InvoiceModel::where('status', 'pending')->count(),
            'revenue' => InvoiceModel::whereDate('invoice_date', today())->sum('total'),
        ];
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /**
     * Create a standalone invoice (not linked to workflow).
     * Does NOT deduct stock — stock deduction is handled by InvoiceStep (workflow)
     * or PharmacyService (prescription path).
     */
    public function create(array $data): InvoiceModel
    {
        return DB::transaction(function () use ($data) {
            $clinicId = currentClinic()->id;
            $code     = ClinicCodeService::invoice($clinicId);

            $invoice = InvoiceModel::create([
                'code'         => $code,
                'patient_code' => $data['patient_code'],
                'visit_code'   => $data['visit_code'] ?? null,
                'payment_type' => $data['payment_type'],
                'invoice_date' => $data['invoice_date'] ?? today(),
                'due_date'     => $data['due_date'] ?? null,
                'cashier'      => $data['cashier'] ?? auth()->user()?->name,
                'notes'        => $data['notes'] ?? null,
                'discount_total' => (float)($data['discount_total'] ?? 0),
                'tax_total'      => (float)($data['tax_total'] ?? 0),
                'status'       => 'pending',
                'subtotal'     => 0,
                'total'        => 0,
            ]);

            $this->syncLineItems($invoice, $data);
            $invoice->recalculateTotal();

            return $invoice->fresh(['services', 'medications']);
        });
    }

    /**
     * Update invoice line items and header.
     * Only allowed when status is pending or partial.
     *
     * @throws \RuntimeException if invoice is paid or voided
     */
    public function update(InvoiceModel $invoice, array $data): InvoiceModel
    {
        if (in_array($invoice->status, ['paid', 'void'])) {
            throw new \RuntimeException(
                "Cannot edit a {$invoice->status} invoice."
            );
        }

        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'payment_type'   => $data['payment_type'] ?? $invoice->payment_type,
                'invoice_date'   => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date'       => $data['due_date'] ?? $invoice->due_date,
                'cashier'        => $data['cashier'] ?? $invoice->cashier,
                'notes'          => $data['notes'] ?? $invoice->notes,
                'discount_total' => (float)($data['discount_total'] ?? $invoice->discount_total),
                'tax_total'      => (float)($data['tax_total'] ?? $invoice->tax_total),
            ]);

            $invoice->services()->forceDelete();
            $invoice->medications()->forceDelete();

            $this->syncLineItems($invoice, $data);
            $invoice->recalculateTotal();

            // Re-reconcile payment status after total changed
            $invoice->refresh();
            $paid = $invoice->payments()->sum('amount');
            $invoice->update(['status' => match (true) {
                $paid >= $invoice->total && $paid > 0 => 'paid',
                $paid > 0                             => 'partial',
                default                               => 'pending',
            }]);

            return $invoice->fresh(['services', 'medications', 'payments']);
        });
    }

    /**
     * Void an invoice.
     * Returns stock if medicines were deducted (via StockService::returnStock).
     *
     * @throws \RuntimeException if invoice is already paid
     */
    public function void(InvoiceModel $invoice): void
    {
        if ($invoice->status === 'void') {
            throw new \RuntimeException('Invoice is already voided.');
        }

        if ($invoice->status === 'paid') {
            throw new \RuntimeException('Cannot void a fully paid invoice. Issue a refund instead.');
        }

        DB::transaction(function () use ($invoice) {
            // Return stock if any medicines were deducted through this invoice
            StockService::returnStock($invoice->code);

            $invoice->update(['status' => 'void']);
        });
    }

    /**
     * Collect a payment against an invoice (transactional + locked).
     *
     * Uses SELECT ... FOR UPDATE on the invoice row to prevent concurrent
     * double-collection. Amount is capped at the remaining balance.
     *
     * @throws \RuntimeException if invoice is already paid or voided
     */
    public function collectPayment(string $invoiceCode, array $data): PaymentModel
    {
        return DB::transaction(function () use ($invoiceCode, $data) {
            // Lock the invoice row for the duration of this transaction
            $invoice = InvoiceModel::where('code', $invoiceCode)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->status === 'void') {
                throw new \RuntimeException('Cannot collect payment on a voided invoice.');
            }

            // Compute real balance inside the lock (stale-read prevention)
            $alreadyPaid = $invoice->payments()->sum('amount');
            $balance     = $invoice->total - $alreadyPaid;

            if ($balance <= 0) {
                throw new \RuntimeException('Invoice is already fully paid.');
            }

            // Cap amount to avoid over-collection
            $amount = min((float)$data['amount'], $balance);

            $payment = PaymentModel::create([
                'code'         => ClinicCodeService::payment(currentClinic()->id),
                'invoice_code' => $invoiceCode,
                'patient_code' => $invoice->patient_code,
                'amount'       => $amount,
                'method'       => $data['method'],
                'reference'    => $data['reference'] ?? null,
                'note'         => $data['note'] ?? null,
                'collected_by' => $data['collected_by'] ?? auth()->user()?->name,
                'paid_at'      => now(),
            ]);

            // Reconcile status using the now-accurate totals
            $newPaid = $alreadyPaid + $amount;
            $invoice->update(['status' => match (true) {
                $newPaid >= $invoice->total => 'paid',
                $newPaid > 0               => 'partial',
                default                    => 'pending',
            }]);

            return $payment;
        });
    }

    // ── Old alias (kept for InvoiceStep backward compat) ─────────────────────

    /**
     * @deprecated  Use create() for new code. Kept for InvoiceStep compatibility.
     */
    public function createFromVisit(string $visitCode, array $data): InvoiceModel
    {
        return DB::transaction(function () use ($visitCode, $data) {
            $clinicId = currentClinic()->id;
            $invCode  = ClinicCodeService::invoice($clinicId);

            $invoice = InvoiceModel::create([
                'code'         => $invCode,
                'patient_code' => $data['patient_code'],
                'visit_code'   => $visitCode,
                'payment_type' => $data['payment_type'],
                'invoice_date' => $data['invoice_date'] ?? today(),
                'cashier'      => $data['cashier'] ?? auth()->user()?->name,
                'status'       => 'pending',
            ]);

            $total = 0;

            foreach ($data['services'] ?? [] as $svc) {
                if (empty(trim($svc['service_name'] ?? ''))) continue;
                $price = (float)($svc['price'] ?? 0);
                $qty   = (float)($svc['qty'] ?? 1);
                $total += $price * $qty;  // Fixed: was $total += $price (ignored qty)

                InvoiceServiceModel::create([
                    'invoice_code'     => $invCode,
                    'service_code'     => $svc['service_code'] ?? null,
                    'service_name'     => $svc['service_name'],
                    'service_category' => $svc['service_category'] ?? null,
                    'qty'              => $qty,
                    'price'            => $price,
                ]);
            }

            foreach ($data['medications'] ?? [] as $med) {
                if (empty(trim($med['medicine_name'] ?? ''))) continue;
                $price = (float)($med['price'] ?? 0);
                $qty   = (float)($med['quantity'] ?? 1);
                $total += $price * $qty;

                InvoiceMedicationModel::create([
                    'invoice_code'  => $invCode,
                    'medicine_id'   => $med['medicine_id'] ?? null,
                    'medicine_code' => $med['medicine_code'] ?? null,
                    'medicine_name' => $med['medicine_name'],
                    'quantity'      => $qty,
                    'price'         => $price,
                ]);
            }

            $invoice->update(['subtotal' => $total, 'total' => $total]);

            return $invoice;
        });
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Write service + medication line items from validated form data.
     * Shared by create() and update().
     */
    private function syncLineItems(InvoiceModel $invoice, array $data): void
    {
        foreach ($data['services'] ?? [] as $svc) {
            if (empty(trim($svc['name'] ?? ''))) continue;
            InvoiceServiceModel::create([
                'invoice_code'     => $invoice->code,
                'service_code'     => $svc['service_code'] ?? null,
                'service_name'     => $svc['name'],
                'service_category' => $svc['category'] ?? null,
                'qty'              => (float)($svc['qty'] ?? 1),
                'price'            => (float)($svc['price'] ?? 0),
            ]);
        }

        foreach ($data['inv_meds'] ?? [] as $med) {
            if (empty(trim($med['name'] ?? ''))) continue;
            InvoiceMedicationModel::create([
                'invoice_code'  => $invoice->code,
                'medicine_id'   => $med['medicine_id'] ?? null,
                'medicine_code' => $med['medicine_code'] ?? null,
                'medicine_name' => $med['name'],
                'quantity'      => (float)($med['qty'] ?? 1),
                'price'         => (float)($med['price'] ?? 0),
            ]);
        }
    }
}
