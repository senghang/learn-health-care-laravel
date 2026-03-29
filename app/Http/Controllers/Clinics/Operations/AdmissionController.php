<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Models\BedModel;
use App\Models\WardModel;
use App\Services\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdmissionController — thin. ALL logic in AdmissionService.
 */
class AdmissionController extends Controller
{
    public function __construct(
        private readonly AdmissionService $admissionService
    ) {}

    /**
     * GET /admissions — list active and recent admissions.
     */
    public function index(Request $request): View
    {
        $admissions = $this->admissionService->list($request->all());
        $stats = $this->admissionService->stats();
        $wards = WardModel::where('is_active', true)->orderBy('name')->get();

        return view('clinics.ipd.admissions', compact('admissions', 'stats', 'wards'));
    }

    /**
     * GET /admissions/{code} — admission detail with treatments + meds.
     */
    public function show(string $code): View
    {
        $admission = $this->admissionService->findByCode($code);

        return view('clinics.ipd.admission-show', compact('admission'));
    }

    /**
     * POST /admissions/{visitCode}/admit — create admission from an IPD visit.
     */
    public function admit(Request $request, string $visitCode): RedirectResponse
    {
        $data = $request->validate([
            'bed_id'                => 'nullable|integer|exists:beds,id',
            'ward_id'               => 'nullable|integer|exists:wards,id',
            'admission_type'        => 'nullable|string|max:40',
            'admission_reason'      => 'nullable|string|max:500',
            'attending_doctor'      => 'nullable|string|max:120',
            'admitting_doctor'      => 'nullable|string|max:120',
            'primary_nurse'         => 'nullable|string|max:120',
            'admitted_at'           => 'nullable|date',
            'expected_discharge_at' => 'nullable|date|after:admitted_at',
        ]);

        $data['visit_code'] = $visitCode;

        try {
            $admission = $this->admissionService->admit($data);

            return redirect()->route('admissions.show', $admission->code)
                ->with('flash', "Patient admitted: {$admission->code}");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['admission' => $e->getMessage()])->withInput();
        }
    }

    /**
     * POST /admissions/{code}/discharge — discharge patient.
     */
    public function discharge(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'discharge_type'      => 'required|string|max:40',
            'discharge_summary'   => 'nullable|string',
            'discharge_condition' => 'nullable|string|max:40',
            'visit_outcome'       => 'nullable|string|max:80',
            'discharged_at'       => 'nullable|date',
        ]);

        try {
            $admission = $this->admissionService->discharge($code, $data);

            return redirect()->route('admissions.index')
                ->with('flash', "Patient discharged: {$admission->code}");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['discharge' => $e->getMessage()]);
        }
    }

    /**
     * POST /admissions/{code}/treatment — add treatment order.
     */
    public function addTreatment(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'treatment_type' => 'required|string|max:60',
            'name'           => 'required|string|max:200',
            'instructions'   => 'nullable|string',
            'frequency'      => 'nullable|string|max:60',
            'route'          => 'nullable|string|max:40',
            'duration'       => 'nullable|string|max:60',
            'notes'          => 'nullable|string',
        ]);

        $this->admissionService->addTreatment($code, $data);

        return back()->with('flash', 'Treatment added.');
    }

    /**
     * POST /admissions/{code}/medication — add IPD medication.
     */
    public function addMedication(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:medicines,id',
            'dosage'      => 'nullable|string|max:60',
            'route'       => 'nullable|string|max:40',
            'frequency'   => 'nullable|string|max:60',
            'quantity'    => 'nullable|numeric|min:0.01',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
        ]);

        $this->admissionService->addMedication($code, $data);

        return back()->with('flash', 'Medication added.');
    }

    /**
     * PATCH /admissions/{code}/transfer-bed — transfer to different bed.
     */
    public function transferBed(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'bed_id' => 'required|integer|exists:beds,id',
        ]);

        try {
            $this->admissionService->transferBed($code, $data['bed_id']);

            return back()->with('flash', 'Bed transferred.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }
}
