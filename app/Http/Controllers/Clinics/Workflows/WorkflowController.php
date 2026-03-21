<?php

namespace App\Http\Controllers\Clinics\Workflows;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function __construct(private readonly WorkflowStepRegistry $registry)
    {
    }

    /** GET /workflow/create */
    public function create(): View
    {
        $nextCode = $this->generateVisitCode();
        $steps    = $this->registry->all();

        return view('clinics.workflow.create', compact('steps', 'nextCode'));
    }

    /** POST /workflow */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_code'   => 'required|string|max:30',
            'surname'        => 'required|string|max:120',
            'given_name'     => 'required|string|max:120',
            'visit_type'     => 'required|in:OPD,IPD',
            'admission_type' => 'nullable|string',
            'admitted_at'    => 'nullable|date',
            'sex'            => 'nullable|in:F,M',
            'birthdate'      => 'nullable|date',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
        ]);

        PatientModel::updateOrCreate(
            ['code' => $data['patient_code']],
            array_filter([
                'clinic_id'   => currentClinic()->id,
                'surname'     => $data['surname'],
                'name'        => $data['given_name'],
                'gender'      => $data['sex'] ?? null,
                'birthdate'   => $data['birthdate'] ?? null,
                'phone'       => $data['phone'] ?? null,
                'nationality' => $data['nationality'] ?? null,
            ], fn($v) => $v !== null)
        );

        $code = DB::transaction(function () {
            $count = VisitModel::whereDate('created_at', today())
                ->lockForUpdate()
                ->count();
            return 'V' . now()->format('Ymd') . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        });

        VisitModel::create([
            'code'           => $code,
            'patient_code'   => $data['patient_code'],
            'surname'        => $data['surname'],
            'name'           => $data['given_name'],
            'visit_type'     => $data['visit_type'],
            'admission_type' => $data['admission_type'] ?? 'Self Refer',
            'admitted_at'    => $data['admitted_at'] ?? now(),
            'done_steps'     => [],
            'skipped_steps'  => [],
        ]);

        return redirect()
            ->route('workflow.step', $this->stepParams($code, 'registration'))
            ->with('flash', "✅ ការចូលព្យាបាល {$code} ត្រូវបានបង្កើត / Visit {$code} created")
            ->with('flash_type', 'ok');
    }

    /** GET /workflow/{code}/{step} */
    public function step(string $code, string $step): View
    {
        $visit = $this->findVisit($code);
        $ctx   = WorkflowContext::for($visit, $step, $this->registry->all());

        return view($ctx->view(), $ctx->toViewData());
    }

    /** PATCH /workflow/{code}/{step}/save */
    public function saveStep(Request $request, string $code, string $step): RedirectResponse
    {
        $visit   = $this->findVisit($code);
        $ctx     = WorkflowContext::for($visit, $step, $this->registry->all());
        $stepObj = $this->registry->find($step);

        $ctx->save($request);

        $next = $ctx->nextPendingStep();

        return redirect()
            ->route('workflow.step', $this->stepParams($code, $next?->id() ?? $step))
            ->with('flash', "{$stepObj->labelKm()} ({$stepObj->labelEn()}) saved!")
            ->with('flash_type', 'ok');
    }

    /** GET /workflow/{code}/{step}/skip */
    public function skipStep(string $code, string $step): RedirectResponse
    {
        $visit   = $this->findVisit($code);
        $ctx     = WorkflowContext::for($visit, $step, $this->registry->all());
        $stepObj = $this->registry->find($step);

        $ctx->skip();

        $next = $ctx->nextStep();

        return redirect()
            ->route('workflow.step', $this->stepParams($code, $next?->id() ?? $step))
            ->with('flash', "{$stepObj->labelKm()} skipped — come back later")
            ->with('flash_type', 'wrn');
    }

    /** GET /workflow/{code} — redirect to current pending step */
    public function show(string $code): RedirectResponse
    {
        $visit = $this->findVisit($code);
        $done  = $visit->done_steps ?? [];
        $skip  = $visit->skipped_steps ?? [];

        $next = collect($this->registry->all())
            ->first(fn($s) => !in_array($s->id(), $done) && !in_array($s->id(), $skip))
            ?? collect($this->registry->all())
                ->first(fn($s) => in_array($s->id(), $skip))
            ?? $this->registry->all()[0];

        return redirect()->route('workflow.step', $this->stepParams($code, $next->id()));
    }

    /** GET /workflow */
    public function index(): RedirectResponse
    {
        return redirect()->route('visits.index');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function findVisit(string $code): VisitModel
    {
        return VisitModel::where('code', $code)->firstOrFail();
    }

    private function generateVisitCode(): string
    {
        $count = VisitModel::whereDate('created_at', today())->count();
        return 'V' . now()->format('Ymd') . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Build named route parameters for workflow routes.
     *
     * Routes live under Route::domain('{subdomain}.localhost'), so every
     * route() call has three parameters: subdomain, code, step.
     *
     * Passing a positional array like [$code, $step] maps $code → subdomain
     * and $step → code, breaking the URL.
     *
     * Using named keys ['code' => ..., 'step' => ...] lets Laravel match
     * by name and fills {subdomain} from URL::defaults set by
     * BindSubdomainParameter middleware.
     */
    private function stepParams(string $code, string $step): array
    {
        return ['code' => $code, 'step' => $step];
    }
}
