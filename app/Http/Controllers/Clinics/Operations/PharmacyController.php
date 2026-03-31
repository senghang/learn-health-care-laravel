<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Models\InpatientMedicationModel;
use App\Models\MedicineModel;
use App\Models\PrescriptionModel;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PharmacyController — OPD dispensing queue + IPD medication administration.
 *
 * Routes (auto-registered once this class exists — see routes/clinic.php):
 *   GET  /pharmacy              index   — pending dispensing queue
 *   GET  /pharmacy/{code}       show    — prescription dispense detail
 *   POST /pharmacy/{code}/dispense   dispense — process full or partial dispense
 */
class PharmacyController extends Controller
{
    public function __construct(private PharmacyService $pharmacy)
    {
    }

    // ── Index: Dispensing queue ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending'); // pending|partial|dispensed|all

        $prescriptions = PrescriptionModel::with(['patient', 'medications'])
            ->when($status === 'pending',   fn($q) => $q->whereNull('dispensed_status'))
            ->when($status === 'partial',   fn($q) => $q->where('dispensed_status', 'partial'))
            ->when($status === 'dispensed', fn($q) => $q->where('dispensed_status', 'dispensed'))
            ->when($request->filled('search'), fn($q) =>
                $q->whereHas('patient', fn($p) =>
                    $p->where('surname', 'like', "%{$request->search}%")
                      ->orWhere('name',    'like', "%{$request->search}%")
                      ->orWhere('code',    'like', "%{$request->search}%")
                )->orWhere('code', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('prescribed_at', $request->date)
            )
            ->latest('prescribed_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'pending'   => PrescriptionModel::whereNull('dispensed_status')->count(),
            'partial'   => PrescriptionModel::where('dispensed_status', 'partial')->count(),
            'today'     => PrescriptionModel::where('dispensed_status', 'dispensed')
                            ->whereDate('updated_at', today())->count(),
            'low_stock' => MedicineModel::whereColumn('stock', '<=', 'stock_alert')
                            ->where('is_active', true)->count(),
        ];

        return view('clinics.pharmacy.index', compact('prescriptions', 'stats', 'status'));
    }

    // ── Show: Prescription + stock check ─────────────────────────────────────

    public function show(string $code): View
    {
        $prescription = PrescriptionModel::where('code', $code)
            ->with(['patient', 'visit', 'medications', 'dispenses'])
            ->firstOrFail();

        $stockCheck = $this->pharmacy->checkStock($prescription);

        return view('clinics.pharmacy.show', compact('prescription', 'stockCheck'));
    }

    // ── Dispense: Full or partial ─────────────────────────────────────────────

    public function dispense(Request $request, string $code): RedirectResponse
    {
        $prescription = PrescriptionModel::where('code', $code)
            ->with('medications')
            ->firstOrFail();

        $request->validate([
            'dispensed_by' => 'required|string|max:120',
            'mode'         => 'required|in:full,partial',
            'item_ids'     => 'required_if:mode,partial|array',
            'item_ids.*'   => 'integer',
        ]);

        try {
            if ($request->mode === 'partial') {
                $result = $this->pharmacy->dispensePartial(
                    $prescription,
                    array_map('intval', $request->item_ids ?? []),
                    $request->dispensed_by
                );
            } else {
                $result = $this->pharmacy->dispense($prescription, $request->dispensed_by);
            }

            $dispensedCount = count($result['dispensed']);
            $errorCount     = count($result['errors']);

            if ($errorCount > 0) {
                $msg = "Dispensed {$dispensedCount} item(s). " . implode('; ', $result['errors']);
                return back()->withErrors(['dispense' => $msg])
                             ->with('info', "Partially processed: {$dispensedCount} dispensed.");
            }

            return redirect()->route('pharmacy.show', $code)
                ->with('success', "Dispensed {$dispensedCount} item(s) successfully.");

        } catch (\RuntimeException $e) {
            return back()->withErrors(['dispense' => $e->getMessage()]);
        }
    }

    // ── IPD medication administration ─────────────────────────────────────────

    /**
     * Mark an IPD medication as administered + deduct stock.
     * Called from the admission workflow or ward management screen.
     */
    public function administerIPD(Request $request, string $medCode): RedirectResponse
    {
        $med = InpatientMedicationModel::where('code', $medCode)->firstOrFail();

        $request->validate([
            'administered_by' => 'required|string|max:120',
        ]);

        try {
            $this->pharmacy->administerIPD($med, $request->administered_by);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['administer' => $e->getMessage()]);
        }

        return back()->with('success', "Medication {$med->medicine_name} administered.");
    }
}
