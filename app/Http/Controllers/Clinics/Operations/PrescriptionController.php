<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Models\MedicineModel;
use App\Models\PrescriptionModel;
use App\Services\ClinicCodeService;
use App\Services\PrescriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    // ── READ ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $prescriptions = PrescriptionModel::with(['patient', 'visit', 'medications'])
            ->when($request->filled('search'), fn($q) =>
                $q->whereHas('patient', fn($p) =>
                    $p->where('surname', 'like', "%{$request->search}%")
                      ->orWhere('name',    'like', "%{$request->search}%")
                      ->orWhere('code',    'like', "%{$request->search}%")
                )
                ->orWhere('code', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('prescribed_at', $request->date)
            )
            ->when($request->filled('doctor'), fn($q) =>
                $q->where('prescribed_by', 'like', "%{$request->doctor}%")
            )
            ->latest('prescribed_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'today'   => PrescriptionModel::whereDate('prescribed_at', today())->count(),
            'total'   => PrescriptionModel::count(),
            'doctors' => PrescriptionModel::whereNotNull('prescribed_by')->distinct()->count('prescribed_by'),
        ];

        return view('clinics.operations.prescriptions', compact('prescriptions', 'stats'));
    }

    public function show(string $code): View
    {
        $prescription = PrescriptionModel::where('code', $code)
            ->with(['patient', 'visit', 'medications'])
            ->firstOrFail();

        return view('clinics.operations.prescription-show', compact('prescription'));
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        $catalog     = $this->medicineCatalog();
        $formOptions = $this->formOptions();

        return view('clinics.operations.prescription-create', compact('catalog', 'formOptions'));
    }

    public function store(StorePrescriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $meds = array_values(array_filter(
            $data['meds'] ?? [],
            fn($m) => trim($m['medicine_name'] ?? '') !== ''
        ));

        $rxCode = DB::transaction(function () use ($data, $meds) {
            $rx = PrescriptionModel::create([
                'code'             => ClinicCodeService::prescription($this->clinicId),
                'patient_code'     => $data['patient_code'],
                'visit_code'       => $data['visit_code'] ?? null,
                'prescribed_at'    => $data['prescribed_at'] ?? now(),
                'prescribed_by'    => $data['prescribed_by'],
                'dispensed_status' => $data['dispensed_status'] ?? null,
                'dispensed_by'     => $data['dispensed_by'] ?? null,
            ]);

            app(PrescriptionService::class)->syncMedications($rx, $meds);

            return $rx->code;
        });

        return redirect()->route('prescriptions.show', $rxCode)
            ->with('success', 'Prescription created successfully.');
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────

    public function edit(string $code): View
    {
        $prescription = PrescriptionModel::where('code', $code)
            ->with(['patient', 'medications'])
            ->firstOrFail();

        $catalog     = $this->medicineCatalog();
        $formOptions = $this->formOptions();

        return view('clinics.operations.prescription-edit',
            compact('prescription', 'catalog', 'formOptions'));
    }

    public function update(UpdatePrescriptionRequest $request, string $code): RedirectResponse
    {
        $prescription = PrescriptionModel::where('code', $code)->firstOrFail();
        $data = $request->validated();

        $meds = array_values(array_filter(
            $data['meds'] ?? [],
            fn($m) => trim($m['medicine_name'] ?? '') !== ''
        ));

        DB::transaction(function () use ($prescription, $data, $meds) {
            $prescription->update([
                'prescribed_at'    => $data['prescribed_at'] ?? $prescription->prescribed_at,
                'prescribed_by'    => $data['prescribed_by'],
                'dispensed_status' => $data['dispensed_status'] ?? null,
                'dispensed_by'     => $data['dispensed_by'] ?? null,
            ]);

            app(PrescriptionService::class)->syncMedications($prescription, $meds);
        });

        return redirect()->route('prescriptions.show', $code)
            ->with('success', 'Prescription updated.');
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function destroy(string $code): RedirectResponse
    {
        $prescription = PrescriptionModel::where('code', $code)->firstOrFail();

        DB::transaction(function () use ($prescription) {
            $prescription->medications()->delete();
            $prescription->delete();
        });

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription deleted.');
    }

    // ── DISPENSE ──────────────────────────────────────────────────────────────

    /**
     * Update dispensing status.
     * When status = 'dispensed', atomically decrements stock for each medication.
     * When status = 'partial', updates the flag without touching stock.
     */
    public function dispense(Request $request, string $code): RedirectResponse
    {
        $prescription = PrescriptionModel::where('code', $code)
            ->with('medications')
            ->firstOrFail();

        $request->validate([
            'dispensed_status' => 'required|in:partial,dispensed',
            'dispensed_by'     => 'nullable|string|max:120',
        ]);

        try {
            if ($request->dispensed_status === 'dispensed') {
                app(PrescriptionService::class)->dispense(
                    $prescription,
                    $request->dispensed_by ?? auth()->user()?->name ?? ''
                );
            } else {
                $prescription->update([
                    'dispensed_status' => 'partial',
                    'dispensed_by'     => $request->dispensed_by,
                ]);
            }
        } catch (\RuntimeException $e) {
            return back()->withErrors(['dispense' => $e->getMessage()]);
        }

        return back()->with('success', 'Dispensing status updated.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function medicineCatalog()
    {
        return MedicineModel::where('is_active', true)
            ->orderBy('form')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'name_kh', 'generic_name', 'form', 'strength', 'unit', 'price', 'stock', 'stock_alert']);
    }

    private function formOptions(): array
    {
        return ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Drops', 'Inhaler', 'Powder'];
    }
}
