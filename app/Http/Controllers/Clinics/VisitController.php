<?php

namespace App\Http\Controllers\Clinics;

use App\Common\Constants\DateFormats;
use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /** GET /visits */
    public function index(Request $request): Factory|View
    {
        $visits = VisitModel::query()
            ->when($request->search, fn($q, $s) =>
                $q->where('surname', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('patient_code', 'like', "%{$s}%")
            )
            ->when($request->type,   fn($q, $t) => $q->where('visit_type', $t))
            ->when($request->status === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when($request->status === 'done',   fn($q) => $q->whereNotNull('discharged_at'))
            ->when($request->date,   fn($q, $d) => $q->whereDate('admitted_at', $d))
            ->latest('admitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('clinics.visits.index', compact('visits'));
    }

    /** GET /visits/{code} */
    public function show(string $code)
    {
        $visit = VisitModel::where('code', $code)
            ->with(['patient', 'diagnoses', 'prescriptions'])
            ->firstOrFail();

        return view('clinics.visits.show', compact('visit'));
    }

    /**
     * GET /patients/search?q=...
     *
     * FIXED: Scoped to currentClinic()->id so users from one subdomain
     * cannot see patients belonging to a different clinic.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clinicId = currentClinic()->id;

        $patients = PatientModel::query()
            ->where('clinic_id', $clinicId)          // ← scoped to current clinic
            ->where(function ($query) use ($q) {
                $query->where('code',    'like', "%{$q}%")
                      ->orWhere('surname', 'like', "%{$q}%")
                      ->orWhere('name',    'like', "%{$q}%")
                      ->orWhere('phone',   'like', "%{$q}%");
            })
            ->with(['address'])
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
                    'name'           => $p->name,           // patients.name = given name
                    'full_name'      => "{$p->surname}, {$p->name}",
                    'sex'            => $p->gender,         // return as 'sex' to match form field
                    'birthdate'      => $p->birthdate?->format(DateFormats::DISPLAY_DATE),
                    'phone'          => $p->phone,
                    'nationality'    => $p->nationality,
                    'occupation'     => $p->occupation,
                    'marital_status' => $p->marital_status,
                    'province_name'  => $p->address->province_name ?? null,
                    'district_name'  => $p->address->district_name ?? null,
                    'commune_name'   => $p->address->commune_name ?? null,
                    'village_name'   => $p->address->village_name ?? null,
                    'house_number'   => $p->address->house_number ?? null,
                    'street_number'  => $p->address->street_number ?? null,
                    'visits_count'   => $p->visits_count,
                    'last_visit_code'=> $lastVisit?->code,
                    'last_visit_date'=> $lastVisit?->admitted_at?->format(DateFormats::DISPLAY_DATE),
                    'last_visit_type'=> $lastVisit?->visit_type,
                ];
            });

        return response()->json($patients);
    }
}
