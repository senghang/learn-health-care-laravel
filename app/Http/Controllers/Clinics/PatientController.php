<?php

namespace App\Http\Controllers\Clinics;

use App\Common\Utils\CodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\PatientModel;
use App\Models\PatientAddressModel;
use App\Models\VisitModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PatientController
 *
 * Full patient CRUD + JSON search endpoint used by the workflow
 * patient autocomplete.
 */
class PatientController extends Controller
{
    // ── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $patients = PatientModel::where('clinic_id', currentClinic()->id)
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($r) => $r
                    ->where('code',    'like', "%{$s}%")
                    ->orWhere('surname','like', "%{$s}%")
                    ->orWhere('name',   'like', "%{$s}%")
                    ->orWhere('phone',  'like', "%{$s}%")
                    ->orWhere('spid',   'like', "%{$s}%")
                )
            )
            ->when($request->sex, fn($q, $v) => $q->where('sex', $v))
            ->withCount('visits')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('clinics.patients.index', compact('patients'));
    }

    // ── Create / store ────────────────────────────────────────────────────────

    public function create(): View
    {
        $nextCode = \App\Services\ClinicCodeService::patientPreview(currentClinic()->id);

        return view('clinics.patients.create', compact('nextCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // code is auto-generated — not accepted from user input
            'surname'        => 'required|string|max:120',
            'name'           => 'required|string|max:120',
            'sex'            => 'required|in:M,F',
            'birthdate'      => 'nullable|date',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
            'occupation'     => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            'spid'           => 'nullable|string|max:30',
            // Address
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',
        ]);

        $patient = PatientModel::create(array_merge(
            collect($data)->only(['surname','name','sex','birthdate',
                'phone','nationality','occupation','marital_status','spid'])->toArray(),
            [
                'clinic_id' => currentClinic()->id,
                'code'      => \App\Services\ClinicCodeService::patient(currentClinic()->id),
            ]
        ));

        PatientAddressModel::create(array_merge(
            collect($data)->only(['province_name','district_name','commune_name',
                'village_name','house_number','street_number'])->toArray(),
            ['patient_code' => $patient->code]
        ));

        return redirect()->route('patients.show', $patient->code)
            ->with('flash', "Patient {$patient->code} created.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(string $code): View
    {
        $patient = PatientModel::where('code', $code)
            ->where('clinic_id', currentClinic()->id)
            ->with(['address', 'identifications', 'visits' => fn($q) => $q->latest('admitted_at')->limit(10)])
            ->firstOrFail();

        return view('clinics.patients.show', compact('patient'));
    }

    // ── Edit / update ─────────────────────────────────────────────────────────

    public function edit(string $code): View
    {
        $patient = PatientModel::where('code', $code)
            ->where('clinic_id', currentClinic()->id)
            ->with('address')
            ->firstOrFail();

        return view('clinics.patients.edit', compact('patient'));
    }

    public function update(Request $request, string $code): RedirectResponse
    {
        $patient = PatientModel::where('code', $code)
            ->where('clinic_id', currentClinic()->id)
            ->firstOrFail();

        $data = $request->validate([
            'surname'        => 'required|string|max:120',
            'name'           => 'required|string|max:120',
            'sex'            => 'required|in:M,F',
            'birthdate'      => 'nullable|date',
            'phone'          => 'nullable|string|max:30',
            'nationality'    => 'nullable|string|max:80',
            'occupation'     => 'nullable|string|max:120',
            'marital_status' => 'nullable|string|max:30',
            'spid'           => 'nullable|string|max:30',
            'province_name'  => 'nullable|string|max:100',
            'district_name'  => 'nullable|string|max:100',
            'commune_name'   => 'nullable|string|max:100',
            'village_name'   => 'nullable|string|max:100',
            'house_number'   => 'nullable|string|max:20',
            'street_number'  => 'nullable|string|max:20',
        ]);

        $patient->update(collect($data)->only([
            'surname','name','sex','birthdate','phone',
            'nationality','occupation','marital_status','spid',
        ])->toArray());

        PatientAddressModel::updateOrCreate(
            ['patient_code' => $patient->code],
            collect($data)->only([
                'province_name','district_name','commune_name',
                'village_name','house_number','street_number',
            ])->toArray()
        );

        return redirect()->route('patients.show', $patient->code)
            ->with('flash', 'Patient updated.');
    }

    // ── JSON search (autocomplete) ─────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = PatientModel::where('clinic_id', currentClinic()->id)
            ->where(fn($r) => $r
                ->where('code',    'like', "%{$q}%")
                ->orWhere('surname','like', "%{$q}%")
                ->orWhere('name',   'like', "%{$q}%")
                ->orWhere('phone',  'like', "%{$q}%")
            )
            ->with('address')
            ->withCount('visits')
            ->orderByDesc('visits_count')
            ->limit(8)
            ->get()
            ->map(function (PatientModel $p) {
                $lastVisit = VisitModel::where('patient_code', $p->code)
                    ->latest('admitted_at')->first();

                return [
                    'code'            => $p->code,
                    'surname'         => $p->surname,
                    'name'            => $p->name,
                    'full_name'       => "{$p->surname}, {$p->name}",
                    'sex'             => $p->sex,
                    'birthdate'       => $p->birthdate?->format('d/m/Y'),
                    'phone'           => $p->phone,
                    'nationality'     => $p->nationality,
                    'occupation'      => $p->occupation,
                    'marital_status'  => $p->marital_status,
                    'province_name'   => $p->address?->province_name,
                    'district_name'   => $p->address?->district_name,
                    'commune_name'    => $p->address?->commune_name,
                    'village_name'    => $p->address?->village_name,
                    'house_number'    => $p->address?->house_number,
                    'street_number'   => $p->address?->street_number,
                    'visits_count'    => $p->visits_count,
                    'last_visit_code' => $lastVisit?->code,
                    'last_visit_date' => $lastVisit?->admitted_at?->format('d/m/Y'),
                    'last_visit_type' => $lastVisit?->visit_type,
                ];
            });

        return response()->json($patients);
    }
}
