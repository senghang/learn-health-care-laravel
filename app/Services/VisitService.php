<?php

namespace App\Services;

use App\Models\OutInPatientModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * VisitService — OPD/IPD visit lifecycle.
 *
 * Handles: creation, listing, filtering, discharge, status transitions.
 * Controllers should delegate ALL business logic here.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * CHANGES FROM PREVIOUS VERSION:
 *   ✅ Fixed: create() now sets clinic_id on visit (was missing)
 *   ✅ Fixed: create() uses 'sex' not 'gender' for patient upsert
 *   ✅ Added: discharge() method with proper validation
 *   ✅ Added: updatePriority() for triage-level sorting
 *   ✅ Added: saveClinicalSummary() for discharge documentation
 *   ✅ Added: todayStats() for dashboard cards
 *   ✅ All existing method signatures preserved (backward compat)
 * ═══════════════════════════════════════════════════════════════════════════════
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
                    ->where('surname',       'ilike', "%{$s}%")
                    ->orWhere('name',        'ilike', "%{$s}%")
                    ->orWhere('code',        'ilike', "%{$s}%")
                    ->orWhere('patient_code','ilike', "%{$s}%")
                )
            )
            ->when($filters['type'] ?? null,     fn($q, $t) => $q->where('visit_type', $t))
            ->when($filters['priority'] ?? null, fn($q, $p) => $q->where('priority', $p))
            ->when(($filters['status'] ?? null) === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when(($filters['status'] ?? null) === 'done',   fn($q) => $q->whereNotNull('discharged_at'))
            ->when($filters['date'] ?? null,     fn($q, $d) => $q->whereDate('admitted_at', $d))
            ->with('patient')
            ->latest('admitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a visit by code with patient relation.
     */
    public function findByCode(string $code): VisitModel
    {
        return VisitModel::where('code', $code)
            ->with(['patient', 'diagnoses', 'prescriptions.medications'])
            ->firstOrFail();
    }

    /**
     * Create a new OPD/IPD visit (transactional).
     *
     * Upserts the patient record if demographics provided.
     * Creates the encounter record automatically.
     */
    public function create(array $data): VisitModel
    {
        return DB::transaction(function () use ($data) {
            $clinicId = currentClinic()->id;

            // Upsert patient demographics (FIX: use 'sex' not 'gender')
            PatientModel::updateOrCreate(
                ['code' => $data['patient_code']],
                array_filter([
                    'clinic_id'   => $clinicId,
                    'surname'     => $data['surname'],
                    'name'        => $data['given_name'] ?? $data['name'] ?? null,
                    'sex'         => $data['sex'] ?? null,
                    'birthdate'   => $data['birthdate'] ?? null,
                    'phone'       => $data['phone'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                ], fn($v) => $v !== null)
            );

            $code = ClinicCodeService::visit($clinicId);

            $visit = VisitModel::create([
                'clinic_id'      => $clinicId,  // FIX: was missing
                'code'           => $code,
                'patient_code'   => $data['patient_code'],
                'surname'        => $data['surname'],
                'name'           => $data['given_name'] ?? $data['name'] ?? '',
                'visit_type'     => $data['visit_type'],
                'admission_type' => $data['admission_type'] ?? null,
                'admitted_at'    => $data['admitted_at'] ?? now(),
                'done_steps'     => [],
                'skipped_steps'  => [],
                'created_by'     => auth()->id(),
            ]);

            // Auto-create encounter
            $prefix = $visit->visit_type === 'IPD' ? 'IPD' : 'OPD';
            OutInPatientModel::firstOrCreate(
                ['code' => "{$prefix}-{$visit->code}"],
                [
                    'visit_code' => $visit->code,
                    'visit_type' => $visit->visit_type,
                    'started_at' => $visit->admitted_at ?? now(),
                    'title'      => "{$prefix} — {$visit->surname}, {$visit->name}",
                ]
            );

            return $visit;
        });
    }

    /**
     * Discharge a visit (transactional).
     *
     * @throws \RuntimeException if already discharged
     */
    public function discharge(string $code, array $data): VisitModel
    {
        return DB::transaction(function () use ($code, $data) {
            $visit = VisitModel::where('code', $code)->firstOrFail();

            if ($visit->discharged_at) {
                throw new \RuntimeException("Visit {$code} is already discharged.");
            }

            $visit->update([
                'discharged_at'    => $data['discharged_at'] ?? now(),
                'discharge_type'   => $data['discharge_type'] ?? null,
                'visit_outcome'    => $data['visit_outcome'] ?? null,
                'clinical_summary' => $data['clinical_summary'] ?? null,
                'followup_at'      => $data['followup_at'] ?? null,
                'updated_by'       => auth()->id(),
            ]);

            // Close the encounter
            $encounter = OutInPatientModel::where('visit_code', $code)->first();
            if ($encounter) {
                $encounter->update([
                    'ended_at' => $data['discharged_at'] ?? now(),
                    'status'   => 'completed',
                ]);
            }

            return $visit->fresh('patient');
        });
    }

    /**
     * Update visit priority (set at triage step).
     */
    public function updatePriority(string $code, string $priority): void
    {
        VisitModel::where('code', $code)->update(['priority' => $priority]);
    }

    /**
     * Save clinical summary text (discharge documentation).
     */
    public function saveClinicalSummary(string $code, string $summary): void
    {
        VisitModel::where('code', $code)->update([
            'clinical_summary' => $summary,
            'updated_by'       => auth()->id(),
        ]);
    }

    /**
     * Today's statistics for dashboard cards.
     */
    public function todayStats(): array
    {
        $today = today();

        return [
            'total'     => VisitModel::whereDate('admitted_at', $today)->count(),
            'opd'       => VisitModel::whereDate('admitted_at', $today)->where('visit_type', 'OPD')->count(),
            'ipd'       => VisitModel::whereDate('admitted_at', $today)->where('visit_type', 'IPD')->count(),
            'active'    => VisitModel::whereDate('admitted_at', $today)->whereNull('discharged_at')->count(),
            'completed' => VisitModel::whereDate('admitted_at', $today)->whereNotNull('discharged_at')->count(),
            'emergency' => VisitModel::whereDate('admitted_at', $today)->where('priority', 'Emergency')->count(),
        ];
    }

    /**
     * Search visits for autocomplete (JSON).
     */
    public function search(string $query, int $limit = 10): \Illuminate\Support\Collection
    {
        return VisitModel::where(fn($q) => $q
                ->where('code',        'ilike', "%{$query}%")
                ->orWhere('surname',   'ilike', "%{$query}%")
                ->orWhere('name',      'ilike', "%{$query}%")
                ->orWhere('patient_code','ilike', "%{$query}%")
            )
            ->with('patient:id,code,surname,name')
            ->limit($limit)
            ->latest('admitted_at')
            ->get(['id', 'code', 'patient_code', 'surname', 'name', 'visit_type', 'admitted_at', 'discharged_at']);
    }
}
