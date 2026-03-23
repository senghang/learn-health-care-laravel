<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Models\InvoiceModel;
use App\Models\InvoiceServiceModel;
use App\Models\PatientModel;
use App\Models\PaymentModel;
use App\Models\ServiceModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    public function index(Request $request): View
    {
        $invoices = InvoiceModel::whereHas('patient', fn($q) =>
                $q->where('clinic_id', $this->clinicId)
            )
            ->with(['patient', 'visit', 'payments'])
            ->when($request->filled('search'), fn($q) =>
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhereHas('patient', fn($p) =>
                      $p->where('surname', 'like', "%{$request->search}%")
                        ->orWhere('name',    'like', "%{$request->search}%")
                  )
            )
            ->when($request->filled('status'),       fn($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_type'), fn($q) => $q->where('payment_type', $request->payment_type))
            ->when($request->filled('date'),         fn($q) => $q->whereDate('invoice_date', $request->date))
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'    => InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))->count(),
            'today'    => InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))->whereDate('invoice_date', today())->count(),
            'pending'  => InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))->where('status', 'pending')->count(),
            'revenue'  => InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))->whereDate('invoice_date', today())->sum('total'),
        ];

        return view('clinics.operations.invoices', compact('invoices', 'stats'));
    }

    public function show(string $code): View
    {
        $invoice = InvoiceModel::whereHas('patient', fn($q) =>
                $q->where('clinic_id', $this->clinicId)
            )
            ->where('code', $code)
            ->with(['patient', 'visit', 'services', 'medications', 'payments'])
            ->firstOrFail();

        $services = ServiceModel::where('clinic_id', $this->clinicId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('clinics.operations.invoice-show', compact('invoice', 'services'));
    }

    // ── Payment collection ────────────────────────────────────────────────────

    public function collectPayment(Request $request, string $code): RedirectResponse
    {
        $invoice = InvoiceModel::whereHas('patient', fn($q) =>
                $q->where('clinic_id', $this->clinicId)
            )
            ->where('code', $code)->firstOrFail();

        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:CASH,HEF,NSSF,CARD,BAKONG',
            'reference'    => 'nullable|string|max:80',
            'note'         => 'nullable|string',
            'collected_by' => 'nullable|string|max:120',
        ]);

        PaymentModel::create(array_merge($data, [
            'code'         => 'PAY-' . $invoice->code . '-' . now()->timestamp,
            'clinic_id'    => $this->clinicId,
            'invoice_code' => $invoice->code,
            'patient_code' => $invoice->patient_code,
            'paid_at'      => now(),
            'collected_by' => $data['collected_by'] ?? auth()->user()?->name,
        ]));

        // Update invoice status
        $paid  = $invoice->payments()->sum('amount') + $data['amount'];
        $status = match (true) {
            $paid  >= $invoice->total => 'paid',
            $paid  > 0               => 'partial',
            default                  => 'pending',
        };
        $invoice->update(['status' => $status]);

        return redirect()->route('invoices.show', $code)
            ->with('flash', "Payment of " . number_format($data['amount']) . " KHR recorded.");
    }
}
