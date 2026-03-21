<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * WorkflowController — refactored with Strategy pattern.
 *
 * The controller is now thin. It only:
 *   1. Resolves the Visit + step from the URL
 *   2. Builds a WorkflowContext (which holds the active step strategy)
 *   3. Delegates save/skip to the context
 *
 * Adding a new clinical step = add a class + register it in WorkflowStepRegistry.
 * Zero changes needed here.
 */
class WorkflowController extends Controller
{
    public function __construct(private readonly WorkflowStepRegistry $registry)
    {
    }

    /** POST /workflow */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_code' => 'required|string|max:30',
            'surname' => 'required|string|max:120',
            'given_name' => 'required|string|max:120',
            'visit_type' => 'required|in:OPD,IPD',
            'admission_type' => 'nullable|string',
            'admitted_at' => 'nullable|date',
            'gender' => 'nullable|in:F,M',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:80',
        ]);

        // Upsert patient — create if new, update demographics if existing
        PatientModel::updateOrCreate(
            ['code' => $data['patient_code']],
            array_filter([
                'clinic_id' => currentClinic()->id,
                'surname' => $data['surname'],
                'name' => $data['given_name'],
                'gender' => $data['sex'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'phone' => $data['phone'] ?? null,
                'nationality' => $data['nationality'] ?? null,
            ], fn($v) => $v !== null)
        );

        $code = 'V' . now()->format('Ymd') . str_pad(
                VisitModel::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT
            );
        VisitModel::create([
            'code' => $code,
            'patient_code' => $data['patient_code'],
            'surname' => $data['surname'],
            'given_name' => $data['given_name'],
            'visit_type' => $data['visit_type'],
            'admission_type' => $data['admission_type'] ?? 'Self Refer',
            'admitted_at' => $data['admitted_at'] ?? now(),
            'done_steps' => [],
            'skipped_steps' => [],
        ]);

        return redirect()
            ->route('workflow.step', [$code, 'registration'])
            ->with('flash', "✅ ការចូលព្យាបាល {$code} ត្រូវបានបង្កើត / Visit {$code} created")
            ->with('flash_type', 'ok');
    }

    /** GET /workflow/create */
    public function create(): View
    {
        $nextCode = 'V' . now()->format('Ymd') . str_pad(
                VisitModel::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT
            );
        $steps = $this->registry->all();
        return view('clinics.workflow.create', compact('steps', 'nextCode'));
    }

    /** GET /workflow/{code}/{step} */
    public function step(string $code, string $step): View
    {
        $visit = $this->findVisit($code);
        $ctx = WorkflowContext::for($visit, $step, $this->registry->all());

        return view($ctx->view(), $ctx->toViewData());
    }

    private function findVisit(string $code): VisitModel
    {
        return VisitModel::where('code', $code)->firstOrFail();
    }

    /** PATCH /workflow/{code}/{step}/save */
    public function saveStep(Request $request, string $code, string $step): RedirectResponse
    {
        $visit = $this->findVisit($code);
        $ctx = WorkflowContext::for($visit, $step, $this->registry->all());
        $stepObj = $this->registry->find($step);

        $ctx->save($request);

        $next = $ctx->nextPendingStep();

        return redirect()
            ->route('workflow.step', [$code, $next?->id() ?? $step])
            ->with('flash', "{$stepObj->labelKm()} ({$stepObj->labelEn()}) saved!")
            ->with('flash_type', 'ok');
    }

    /** GET /workflow/{code}/{step}/skip */
    public function skipStep(string $code, string $step): RedirectResponse
    {
        $visit = $this->findVisit($code);
        $ctx = WorkflowContext::for($visit, $step, $this->registry->all());
        $stepObj = $this->registry->find($step);

        $ctx->skip();

        $next = $ctx->nextStep();

        return redirect()
            ->route('workflow.step', [$code, $next?->id() ?? $step])
            ->with('flash', "{$stepObj->labelKm()} skipped — come back later")
            ->with('flash_type', 'wrn');
    }

    /** GET /workflow/{code} — redirect to current pending step */
    public function show(string $code): RedirectResponse
    {
        $visit = $this->findVisit($code);
        $done = $visit->done_steps ?? [];
        $skip = $visit->skipped_steps ?? [];

        $next = collect($this->registry->all())
            ->first(fn($s) => !in_array($s->id(), $done) && !in_array($s->id(), $skip))
            ?? collect($this->registry->all())->first(fn($s) => in_array($s->id(), $skip))
            ?? $this->registry->all()[0];

        return redirect()->route('workflow.step', [$code, $next->id()]);
    }
}
