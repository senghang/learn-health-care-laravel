<?php

namespace App\Http\Controllers\Clinics\Print;

use App\Http\Controllers\Controller;
use App\Models\InvoiceModel;
use App\Models\PrescriptionModel;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * PrintController
 *
 * Serves ready-to-print HTML pages for prescriptions and invoices.
 * The browser opens the URL in a new tab; the page auto-triggers window.print().
 *
 * Routes (added to clinic.php):
 *   GET /print/prescription/{code}   → PrintController@prescription
 *   GET /print/invoice/{code}        → PrintController@invoice
 */
class PrintController extends Controller
{
    public function __construct(private readonly PrintService $print) {}

    // ── Prescription ──────────────────────────────────────────────────────────

    public function prescription(Request $request, string $code): Response
    {
        $rx = PrescriptionModel::where('code', $code)
            ->with(['medications', 'visit.patient'])
            ->firstOrFail();

        $locale = $request->get('locale', app()->getLocale());

        $html = $this->print->prescription($rx, $locale);

        $this->logPrint('Prescription', $rx->code);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // ── Invoice ───────────────────────────────────────────────────────────────

    public function invoice(Request $request, string $code): Response
    {
        $invoice = InvoiceModel::where('code', $code)
            ->with(['services', 'medications', 'visit.patient'])
            ->firstOrFail();

        $locale = $request->get('locale', app()->getLocale());

        $html = $this->print->invoice($invoice, $locale);

        $this->logPrint('Invoice', $invoice->code);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function logPrint(string $model, string $code): void
    {
        try {
            \App\Models\AuditLogModel::create([
                'clinic_id' => currentClinic()->id,
                'user_id'   => auth()->id(),
                'model'     => $model,
                'model_id'  => $code,
                'event'     => 'printed',
                'ip_address'=> request()->ip(),
            ]);
        } catch (\Throwable) {}
    }
}
