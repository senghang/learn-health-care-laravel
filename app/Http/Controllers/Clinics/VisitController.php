<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /** GET /visits — list with search/filter */
    public function index(Request $request): Factory|View
    {
        $visits = VisitModel::query()
            ->when($request->search, fn($q, $s) => $q->where('surname', 'like', "%{$s}%")
                ->orWhere('given_name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")
                ->orWhere('patient_code', 'like', "%{$s}%")
            )
            ->when($request->type, fn($q, $t) => $q->where('visit_type', $t))
            ->when($request->status === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when($request->status === 'done', fn($q) => $q->whereNotNull('discharged_at'))
            ->when($request->date, fn($q, $d) => $q->whereDate('admitted_at', $d))
            ->latest('admitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('clinics.visits.index', compact('visits'));
    }

    /** GET /visits/{code} — single visit detail */
    public function show(string $code)
    {
        $visit = VisitModel::where('code', $code)
            ->with(['patient', 'diagnoses', 'prescriptions'])
            ->firstOrFail();

        return view('clinics.visits.show', compact('visit'));
    }

    /**
     * GET /patients/search?q=...
     * JSON endpoint — returns matching patients for autocomplete.
     * Returns name, code, phone, last visit info.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = PatientModel::query()
            ->where('code', 'like', "%{$q}%")
            ->orWhere('surname', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
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
                    'code' => $p->code,
                    'surname' => $p->surname,
                    'name' => $p->name,
                    'full_name' => "{$p->surname}, {$p->name}",
                    'sex' => $p->sex,
                    'birthdate' => $p->birthdate?->format('d/m/Y'),
                    'phone' => $p->phone,
                    'nationality' => $p->nationality,
                    'occupation' => $p->occupation,
                    'marital_status' => $p->marital_status,
                    'province_name' => $p->address->province_name ?? null,
                    'district_name' => $p->address->district_name ?? null,
                    'commune_name' => $p->address->commune_name ?? null,
                    'village_name' => $p->address->village_name ?? null,
                    'house_number' => $p->address->house_number ?? null,
                    'street_number' => $p->address->street_number ?? null,
                    'visits_count' => $p->visits_count,
                    'last_visit_code' => $lastVisit?->code,
                    'last_visit_date' => $lastVisit?->admitted_at?->format('d/m/Y'),
                    'last_visit_type' => $lastVisit?->visit_type,
                ];
            });

        return response()->json($patients);
    }
}
