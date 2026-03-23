<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use App\Services\ClinicCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
            'sex' => 'nullable|in:F,M',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:80',
        ]);

        PatientModel::updateOrCreate(
            ['code' => $data['patient_code']],
            array_filter([
                'clinic_id' => currentClinic()->id,
                'surname' => $data['surname'],
                'name' => $data['given_name'],
                'sex' => $data['sex'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'phone' => $data['phone'] ?? null,
                'nationality' => $data['nationality'] ?? null,
            ], fn($v) => $v !== null)
        );

        $code = ClinicCodeService::visit(currentClinic()->id);

        VisitModel::create([
            'code' => $code,
            'patient_code' => $data['patient_code'],
            'surname' => $data['surname'],
            'name' => $data['given_name'],
            'visit_type' => $data['visit_type'],
            'admission_type' => $data['admission_type'] ?? 'Self Refer',
            'admitted_at' => $data['admitted_at'] ?? now(),
            'done_steps' => [],
            'skipped_steps' => [],
        ]);

        // Use url() instead of route() to avoid subdomain parameter binding issue
        return redirect($this->stepUrl($code, 'registration'))
            ->with('flash', "✅ ការចូលព្យាបាល {$code} ត្រូវបានបង្កើត / Visit {$code} created")
            ->with('flash_type', 'ok');
    }

    /** GET /workflow/create */
    public function create(): View
    {
        $steps = $this->registry->all();
        $nextCode = $this->generateVisitCode();

        return view('clinics.workflow.create', compact('steps', 'nextCode'));
    }

    private function generateVisitCode(): string
    {
        return ClinicCodeService::visitPreview(currentClinic()->id);
    }

    /**
     * Build a plain URL for a workflow step.
     *
     * Uses url('/workflow/CODE/STEP') instead of route('workflow.step', [...])
     * to completely bypass Laravel's named route parameter binding.
     *
     * Under Route::domain('{subdomain}.localhost'), named routes require
     * the {subdomain} parameter. Even with URL::defaults set by middleware,
     * this is fragile inside controller redirects. Plain url() always works.
     */
    private function stepUrl(string $code, string $step): string
    {
        return url("/workflow/{$code}/{$step}");
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

        try {
            $ctx->save($request);
        } catch (ValidationException $e) {
            // Return back to the same step with validation errors visible
            return redirect($this->stepUrl($code, $step))
                ->withErrors($e->errors())
                ->withInput()
                ->with('flash', '⚠ ' . $stepObj->labelEn() . ': Please fix the errors below.')
                ->with('flash_type', 'err');
        } catch (Throwable $e) {
            // Show a meaningful error instead of a blank page reload
            return redirect($this->stepUrl($code, $step))
                ->withInput()
                ->with('flash', '❌ Could not save: ' . $e->getMessage())
                ->with('flash_type', 'err');
        }

        $next = $ctx->nextPendingStep();

        return redirect($this->stepUrl($code, $next?->id() ?? $step))
            ->with('flash', "{$stepObj->labelKm()} ({$stepObj->labelEn()}) saved!")
            ->with('flash_type', 'ok');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** GET /workflow/{code}/{step}/skip */
    public function skipStep(string $code, string $step): RedirectResponse
    {
        $visit = $this->findVisit($code);
        $ctx = WorkflowContext::for($visit, $step, $this->registry->all());
        $stepObj = $this->registry->find($step);

        $ctx->skip();

        $next = $ctx->nextStep();

        return redirect($this->stepUrl($code, $next?->id() ?? $step))
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
            ?? collect($this->registry->all())
            ->first(fn($s) => in_array($s->id(), $skip))
            ?? $this->registry->all()[0];

        // Use url() — avoids "Missing required parameter: subdomain" error
        // that occurs when using route('workflow.step', [...]) under
        // Route::domain('{subdomain}.localhost')
        return redirect($this->stepUrl($code, $next->id()));
    }

    /** GET /workflow */
    public function index(): RedirectResponse
    {
        return redirect('/visits');
    }
}
