<?php

namespace App\Http\Controllers\Clinics;

use App\Common\Constants\DateFormats;
use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    /**
     * GET /visits
     * List visits scoped to current clinic via patient relationship.
     */
    public function index(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $visits = VisitModel::query()
            // Scope to this clinic's patients only
            ->whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($r) => $r
                    ->where('surname',      'like', "%{$s}%")
                    ->orWhere('name',       'like', "%{$s}%")
                    ->orWhere('code',       'like', "%{$s}%")
                    ->orWhere('patient_code','like', "%{$s}%")
                );
            })
            ->when($request->filled('type'),   fn($q) => $q->where('visit_type', $request->type))
            ->when($request->status === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when($request->status === 'done',   fn($q) => $q->whereNotNull('discharged_at'))
            ->when($request->filled('date'),   fn($q) => $q->whereDate('admitted_at', $request->date))
            ->latest('admitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('clinics.visits.index', compact('visits'));
    }

    /**
     * GET /visits/{code}
     * Redirect to the workflow view — visits are managed via workflow steps.
     */
    public function show(string $code): RedirectResponse
    {
        $clinicId = currentClinic()->id;

        $visit = VisitModel::where('code', $code)
            ->whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
            ->firstOrFail();

        return redirect(url("/workflow/{$visit->code}"));
    }

    /**
     * GET /patients/search/json?q=...
     * JSON endpoint for workflow patient autocomplete.
     * Scoped to current clinic.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clinicId = currentClinic()->id;

        $patients = PatientModel::query()
            ->where('clinic_id', $clinicId)
            ->where(fn($r) => $r
                ->where('code',     'like', "%{$q}%")
                ->orWhere('surname', 'like', "%{$q}%")
                ->orWhere('name',    'like', "%{$q}%")
                ->orWhere('phone',   'like', "%{$q}%")
            )
            ->with('address')
            ->withCount('visits')
            ->orderByDesc('visits_count')
            ->limit(8)
            ->get()
            ->map(function (PatientModel $p) {
                $lastVisit = VisitModel::where('patient_code', $p->code)
                    ->latest('admitted_at')
                    ->first();

                return [
                    'code'           => $p->code,
                    'surname'        => $p->surname,
                    'name'           => $p->name,           // given name stored as `name`
                    'full_name'      => "{$p->surname}, {$p->name}",
                    'sex'            => $p->gender,         // column = gender, returned as sex for form compat
                    'birthdate'      => $p->birthdate?->format(DateFormats::DISPLAY_DATE),
                    'phone'          => $p->phone,
                    'nationality'    => $p->nationality,
                    'occupation'     => $p->occupation,
                    'marital_status' => $p->marital_status,
                    'province_name'  => $p->address?->province_name,
                    'district_name'  => $p->address?->district_name,
                    'commune_name'   => $p->address?->commune_name,
                    'village_name'   => $p->address?->village_name,
                    'house_number'   => $p->address?->house_number,
                    'street_number'  => $p->address?->street_number,
                    'visits_count'   => $p->visits_count,
                    'last_visit_code'=> $lastVisit?->code,
                    'last_visit_date'=> $lastVisit?->admitted_at?->format(DateFormats::DISPLAY_DATE),
                    'last_visit_type'=> $lastVisit?->visit_type,
                ];
            });

        return response()->json($patients);
    }
}
