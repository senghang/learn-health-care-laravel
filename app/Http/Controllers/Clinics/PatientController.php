<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Services\ClinicCodeService;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PatientController — THIN controller.
 *
 * All business logic is delegated to PatientService.
 * Uses FormRequest for validation (StorePatientRequest, UpdatePatientRequest).
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * CHANGES FROM PREVIOUS VERSION:
 *   ✅ Delegates to PatientService (was inline queries)
 *   ✅ Uses FormRequest (was inline validation)
 *   ✅ Added destroy() for soft-delete
 *   ✅ All existing routes preserved (backward compat)
 *   ✅ JSON search endpoint preserved
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patientService
    ) {}

    // ══════════════════════════════════════════════════════════════════════════
    // LIST
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /patients
     */
    public function index(Request $request): View
    {
        $patients = $this->patientService->list([
            'search' => $request->search,
            'sex'    => $request->sex,
            'status' => $request->status,
        ]);

        return view('clinics.patients.index', compact('patients'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CREATE / STORE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /patients/create
     */
    public function create(): View
    {
        $nextCode = ClinicCodeService::patientPreview(currentClinic()->id);

        return view('clinics.patients.create', compact('nextCode'));
    }

    /**
     * POST /patients
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = $this->patientService->create(
            $request->patientData(),
            $request->addressData(),
            $request->identificationData(),
            $request->contactData()
        );

        return redirect()->route('patients.show', $patient->code)
            ->with('flash', "Patient {$patient->code} created.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /patients/{code}
     */
    public function show(string $code): View
    {
        $patient = $this->patientService->findForProfile($code);

        return view('clinics.patients.show', compact('patient'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EDIT / UPDATE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /patients/{code}/edit
     */
    public function edit(string $code): View
    {
        $patient = $this->patientService->findByCode($code);

        return view('clinics.patients.edit', compact('patient'));
    }

    /**
     * PATCH /patients/{code}
     */
    public function update(UpdatePatientRequest $request, string $code): RedirectResponse
    {
        $patient = $this->patientService->update(
            $code,
            $request->patientData(),
            $request->addressData(),
            $request->identificationData(),
            $request->contactData()
        );

        return redirect()->route('patients.show', $patient->code)
            ->with('flash', 'Patient updated.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DESTROY (SOFT-DELETE)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * DELETE /patients/{code}
     */
    public function destroy(string $code): RedirectResponse
    {
        try {
            $this->patientService->destroy($code);

            return redirect()->route('patients.index')
                ->with('flash', "Patient {$code} has been deactivated.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }
    }
}
