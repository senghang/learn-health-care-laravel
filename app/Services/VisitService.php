<?php

namespace App\Services;

use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * VisitService — OPD/IPD visit lifecycle.
 *
 * Handles: creation, listing, filtering, discharge, status transitions.
 * Controllers should delegate ALL business logic here.
 */
class VisitService
{
    /**
     * List visits with search/filter.
     */
    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return VisitModel::query()
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where(fn($r) => $r
                    ->where('surname',      'like', "%{$s}%")
                    ->orWhere('name',       'like', "%{$s}%")
                    ->orWhere('code',       'like', "%{$s}%")
                    ->orWhere('patient_code','like', "%{$s}%")
                )
            )
            ->when($filters['type'] ?? null,   fn($q, $t) => $q->where('visit_type', $t))
            ->when(($filters['status'] ?? null) === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when(($filters['status'] ?? null) === 'done',   fn($q) => $q->whereNotNull('discharged_at'))
            ->when($filters['date'] ?? null,   fn($q, $d) => $q->whereDate('admitted_at', $d))
            ->with('patient')
            ->latest('admitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new OPD/IPD visit (transactional).
     *
     * Upserts the patient record if demographics provided.
     */
    public function create(array $data): VisitModel
    {
        return DB::transaction(function () use ($data) {
            // Upsert patient demographics
            PatientModel::updateOrCreate(
                ['code' => $data['patient_code']],
                array_filter([
                    'clinic_id'   => currentClinic()->id,
                    'surname'     => $data['surname'],
                    'name'        => $data['given_name'] ?? $data['name'] ?? null,
                    'gender'      => $data['gender'] ?? null,
                    'birthdate'   => $data['birthdate'] ?? null,
                    'phone'       => $data['phone'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                ], fn($v) => $v !== null)
            );

            $code = ClinicCodeService::visit(currentClinic()->id);

            return VisitModel::create([
                'code'           => $code,
                'patient_code'   => $data['patient_code'],
                'surname'        => $data['surname'],
                'name'           => $data['given_name'] ?? $data['name'] ?? '',
                'visit_type'     => $data['visit_type'],
                'admission_type' => $data['admission_type'] ?? null,
                'admitted_at'    => $data['admitted_at'] ?? now(),
                'done_steps'     => [],
                'skipped_steps'  => [],
            ]);
        });
    }

    /**
     * Find a visit by code.
     */
    public function findByCode(string $code): VisitModel
    {
        return VisitModel::where('code', $code)
            ->with(['patient', 'diagnoses', 'prescriptions', 'invoices'])
            ->firstOrFail();
    }

    /**
     * Discharge a visit (mark as completed).
     */
    public function discharge(string $code, array $data = []): VisitModel
    {
        return DB::transaction(function () use ($code, $data) {
            $visit = VisitModel::where('code', $code)->firstOrFail();

            $visit->update([
                'discharged_at' => $data['discharged_at'] ?? now(),
                'discharge_type' => $data['discharge_type'] ?? 'Normal',
                'visit_outcome'  => $data['visit_outcome'] ?? null,
            ]);

            return $visit;
        });
    }

    /**
     * Get visit statistics for dashboard.
     */
    public function stats(): array
    {
        return [
            'today_visits' => VisitModel::whereDate('admitted_at', today())->count(),
            'active_visits' => VisitModel::whereNull('discharged_at')->count(),
            'inpatients' => VisitModel::where('visit_type', 'IPD')->whereNull('discharged_at')->count(),
            'this_month' => VisitModel::whereMonth('admitted_at', now()->month)
                ->whereYear('admitted_at', now()->year)->count(),
        ];
    }
}
