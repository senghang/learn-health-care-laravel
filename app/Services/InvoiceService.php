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
 * ALL operations are transactional.
 */
class InvoiceService
{
    /**
     * List invoices with filters.
     */
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

    /**
     * Find invoice by code with all relations.
     */
    public function findByCode(string $code): InvoiceModel
    {
        return InvoiceModel::where('code', $code)
            ->with(['patient', 'visit', 'services', 'medications', 'payments'])
            ->firstOrFail();
    }

    /**
     * Create invoice with service + medication line items (transactional).
     */
    public function createFromVisit(string $visitCode, array $data): InvoiceModel
    {
        return DB::transaction(function () use ($visitCode, $data) {
            $invCode = ClinicCodeService::invoice(currentClinic()->id);

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

            // Service line items
            foreach ($data['services'] ?? [] as $svc) {
                if (empty(trim($svc['service_name'] ?? ''))) continue;
                $price = (float)($svc['price'] ?? 0);
                $total += $price;

                InvoiceServiceModel::create([
                    'invoice_code'     => $invCode,
                    'service_code'     => $svc['service_code'] ?? null,
                    'service_name'     => $svc['service_name'],
                    'service_category' => $svc['service_category'] ?? null,
                    'price'            => $price,
                ]);
            }

            // Medication line items
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

            $invoice->update(['total' => $total]);

            return $invoice;
        });
    }

    /**
     * Collect a payment against an invoice (transactional).
     * Auto-updates invoice status based on paid amount.
     */
    public function collectPayment(string $invoiceCode, array $data): PaymentModel
    {
        return DB::transaction(function () use ($invoiceCode, $data) {
            $invoice = InvoiceModel::where('code', $invoiceCode)->firstOrFail();

            $payment = PaymentModel::create([
                'code'         => ClinicCodeService::payment(currentClinic()->id),
                'clinic_id'    => currentClinic()->id,
                'invoice_code' => $invoiceCode,
                'patient_code' => $invoice->patient_code,
                'amount'       => $data['amount'],
                'method'       => $data['method'],
                'reference'    => $data['reference'] ?? null,
                'note'         => $data['note'] ?? null,
                'collected_by' => $data['collected_by'] ?? auth()->user()?->name,
                'paid_at'      => now(),
            ]);

            // Reconcile invoice status
            $totalPaid = $invoice->payments()->sum('amount');
            $status = match (true) {
                $totalPaid >= $invoice->total => 'paid',
                $totalPaid > 0               => 'partial',
                default                      => 'pending',
            };
            $invoice->update(['status' => $status]);

            return $payment;
        });
    }

    /**
     * Get billing statistics.
     */
    public function stats(): array
    {
        return [
            'total_invoices' => InvoiceModel::count(),
            'today_invoices' => InvoiceModel::whereDate('invoice_date', today())->count(),
            'pending_count'  => InvoiceModel::where('status', 'pending')->count(),
            'today_revenue'  => InvoiceModel::whereDate('invoice_date', today())->sum('total'),
        ];
    }
}
