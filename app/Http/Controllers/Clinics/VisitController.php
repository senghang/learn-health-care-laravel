<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use App\Services\OpdSummaryService;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * VisitController — THIN controller for visit listing + clinical summary.
 *
 * NOTE: Visit creation happens through WorkflowController (unchanged).
 * This controller handles listing, show (clinical summary), and discharge.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * CHANGES FROM PREVIOUS VERSION:
 *   ✅ Delegates to VisitService (was inline queries)
 *   ✅ Added show() with full clinical summary via OpdSummaryService
 *   ✅ Added discharge() action
 *   ✅ Preserved JSON search endpoint (searchPatients)
 *   ✅ All existing routes preserved (backward compat)
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class VisitController extends Controller
{
    public function __construct(
        private readonly VisitService $visitService,
        private readonly OpdSummaryService $summaryService,
    ) {}

    /**
     * GET /visits — list with search/filter
     */
    public function index(Request $request): View
    {
        $visits = $this->visitService->list([
            'search'   => $request->search,
            'type'     => $request->type,
            'status'   => $request->status,
            'priority' => $request->priority,
            'date'     => $request->date,
        ]);

        $stats = $this->visitService->todayStats();

        return view('clinics.visits.index', compact('visits', 'stats'));
    }

    /**
     * GET /visits/{code} — visit detail with full clinical summary.
     *
     * Shows all data from all 10 workflow steps in one page.
     * Redirects to workflow if visit is still active (for editing).
     */
    public function show(string $code): View|RedirectResponse
    {
        $visit = $this->summaryService->loadFull($code);

        // If visit is active, redirect to workflow for editing
        if ($visit->isActive()) {
            return redirect(url("/workflow/{$visit->code}"));
        }

        $summary = $this->summaryService->summary($visit);

        return view('clinics.visits.show', compact('visit', 'summary'));
    }

    /**
     * POST /visits/{code}/discharge — discharge an active visit.
     */
    public function discharge(Request $request, string $code): RedirectResponse
    {
        $data = $request->validate([
            'discharge_type'   => 'nullable|string|max:80',
            'visit_outcome'    => 'nullable|string|max:80',
            'clinical_summary' => 'nullable|string',
            'discharged_at'    => 'nullable|date',
            'followup_at'      => 'nullable|date',
        ]);

        try {
            $visit = $this->visitService->discharge($code, $data);

            return redirect()->route('visits.show', $visit->code)
                ->with('flash', "Visit {$code} discharged.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['discharge' => $e->getMessage()]);
        }
    }

    /**
     * GET /patients/search/json?q=...
     * JSON endpoint for workflow patient autocomplete.
     * Preserved from original controller — backward compatible.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $clinicId = currentClinic()->id;

        $patients = PatientModel::where('clinic_id', $clinicId)
            ->where(fn($query) => $query
                ->where('code',    'ilike', "%{$q}%")
                ->orWhere('surname','ilike', "%{$q}%")
                ->orWhere('name',   'ilike', "%{$q}%")
                ->orWhere('phone',  'ilike', "%{$q}%")
            )
            ->withCount('visits')
            ->with(['visits' => fn($v) => $v->latest('admitted_at')->limit(1)])
            ->limit(10)
            ->get();

        return response()->json(
            $patients->map(fn(PatientModel $p) => [
                'code'         => $p->code,
                'surname'      => $p->surname,
                'name'         => $p->name,
                'sex'          => $p->sex,
                'birthdate'    => $p->birthdate?->format('Y-m-d'),
                'age'          => $p->age,
                'phone'        => $p->phone,
                'nationality'  => $p->nationality,
                'visits_count' => $p->visits_count,
                'last_visit'   => $p->visits->first()?->only(['code', 'visit_type', 'admitted_at']),
            ])
        );
    }
}
