<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\InvoiceModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\ServiceModel;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $billing) {}

    // ── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $invoices = $this->billing->list($request->only('search', 'status', 'payment_type', 'date'));
        $stats    = $this->billing->stats();

        return view('clinics.operations.invoices', compact('invoices', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $clinicId   = currentClinic()->id;
        $svcCatalog = ServiceModel::where('clinic_id', $clinicId)->where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'category', 'price']);

        $medCatalog = MedicineModel::where('clinic_id', $clinicId)->where('is_active', true)
            ->orderBy('form')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'generic_name', 'form', 'strength', 'unit', 'price', 'stock', 'stock_alert']);

        // Pre-fill patient if passed via query string (e.g. from patient page)
        $patient = null;
        if ($request->filled('patient_code')) {
            $patient = PatientModel::where('code', $request->patient_code)->first();
        }

        return view('clinics.operations.invoice-create', compact('svcCatalog', 'medCatalog', 'patient'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        try {
            $invoice = $this->billing->create($request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $invoice->code)
            ->with('success', "Invoice {$invoice->code} created.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(string $code): View
    {
        $invoice = $this->billing->findByCode($code);

        return view('clinics.operations.invoice-show', compact('invoice'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(string $code): View
    {
        $invoice = $this->billing->findByCode($code);

        if (in_array($invoice->status, ['paid', 'void'])) {
            return redirect()->route('invoices.show', $code)
                ->with('error', "A {$invoice->status} invoice cannot be edited.");
        }

        $clinicId   = currentClinic()->id;
        $svcCatalog = ServiceModel::where('clinic_id', $clinicId)->where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'category', 'price']);

        $medCatalog = MedicineModel::where('clinic_id', $clinicId)->where('is_active', true)
            ->orderBy('form')->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'generic_name', 'form', 'strength', 'unit', 'price', 'stock', 'stock_alert']);

        return view('clinics.operations.invoice-edit', compact('invoice', 'svcCatalog', 'medCatalog'));
    }

    public function update(UpdateInvoiceRequest $request, string $code): RedirectResponse
    {
        $invoice = $this->billing->findByCode($code);

        try {
            $this->billing->update($invoice, $request->validated());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $code)
            ->with('success', "Invoice {$code} updated.");
    }

    // ── Void ──────────────────────────────────────────────────────────────────

    public function void(string $code): RedirectResponse
    {
        $invoice = InvoiceModel::where('code', $code)->firstOrFail();

        try {
            $this->billing->void($invoice);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $code)
            ->with('success', "Invoice {$code} has been voided.");
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(string $code): RedirectResponse
    {
        $invoice = InvoiceModel::where('code', $code)->firstOrFail();

        if (!in_array($invoice->status, ['pending'])) {
            return back()->with('error', 'Only pending invoices can be deleted.');
        }

        $invoice->services()->forceDelete();
        $invoice->medications()->forceDelete();
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', "Invoice {$code} deleted.");
    }

    // ── Payment ───────────────────────────────────────────────────────────────

    public function collectPayment(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:CASH,HEF,NSSF,CARD,BAKONG',
            'reference'    => 'nullable|string|max:80',
            'note'         => 'nullable|string',
            'collected_by' => 'nullable|string|max:120',
        ]);

        try {
            $this->billing->collectPayment($code, $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $code)
            ->with('success', 'Payment recorded successfully.');
    }
}
