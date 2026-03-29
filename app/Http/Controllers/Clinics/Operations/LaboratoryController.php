<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabOrderRequest;
use App\Services\LaboratoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * LaboratoryController — thin controller.
 * ALL business logic lives in LaboratoryService.
 */
class LaboratoryController extends Controller
{
    public function __construct(
        private readonly LaboratoryService $labService
    ) {}

    public function index(Request $request): View
    {
        $labs = $this->labService->list($request->all());
        $stats = $this->labService->stats();

        return view('clinics.laboratory.index', compact('labs', 'stats'));
    }

    public function show(string $code): View
    {
        $lab = $this->labService->findByCode($code);

        return view('clinics.laboratory.show', compact('lab'));
    }

    public function store(StoreLabOrderRequest $request): RedirectResponse
    {
        $lab = $this->labService->createOrder($request->validated());

        return redirect()->route('laboratory.show', $lab->code)
            ->with('flash', "Lab order {$lab->code} created.");
    }

    public function update(Request $request, string $code): RedirectResponse
    {
        $action = $request->input('action');

        match ($action) {
            'collect' => $this->labService->collectSample($code, $request->input('collected_by')),
            'results' => $this->labService->recordResults($code, $request->input('results', [])),
            'verify'  => $this->labService->verify($code, $request->input('verified_by')),
            default   => abort(400, "Unknown action: {$action}"),
        };

        return back()->with('flash', "Lab order {$code} updated.");
    }
}
