<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Services\ImageryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ImageryController — thin controller.
 * ALL business logic lives in ImageryService.
 */
class ImageryController extends Controller
{
    public function __construct(
        private readonly ImageryService $imageryService
    ) {}

    public function index(Request $request): View
    {
        $imageries = $this->imageryService->list($request->all());
        $stats = $this->imageryService->stats();

        return view('clinics.imagery.index', compact('imageries', 'stats'));
    }

    public function show(string $code): View
    {
        $imagery = $this->imageryService->findByCode($code);

        return view('clinics.imagery.show', compact('imagery'));
    }

    public function create(Request $request): View
    {
        return view('clinics.imagery.create', [
            'patient_code' => $request->input('patient_code'),
            'visit_code'   => $request->input('visit_code'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_code' => 'required|string|exists:patients,code',
            'visit_code'   => 'required|string|exists:visits,code',
            'category'     => 'required|string|max:80',
            'title'        => 'nullable|string|max:200',
            'urgency'      => 'nullable|in:normal,urgent,stat',
            'requested_by' => 'nullable|string|max:120',
        ]);

        $imagery = $this->imageryService->createOrder($data);

        return redirect()->route('imagery.show', $imagery->code)
            ->with('flash', "Imaging order {$imagery->code} created.");
    }

    public function update(Request $request, string $code): RedirectResponse
    {
        $action = $request->input('action');

        match ($action) {
            'result' => $this->imageryService->recordResult($code, $request->only([
                'result', 'conclusion', 'images',
            ])),
            'verify' => $this->imageryService->verify($code, $request->input('verified_by')),
            default  => abort(400, "Unknown action: {$action}"),
        };

        return back()->with('flash', "Imaging order {$code} updated.");
    }
}
