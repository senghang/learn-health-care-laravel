<?php

namespace App\Services;

use App\Models\AdmissionModel;
use App\Models\BedModel;
use App\Models\InpatientMedicationModel;
use App\Models\OutInPatientModel;
use App\Models\TreatmentModel;
use App\Models\VisitModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * AdmissionService — IPD admission lifecycle.
 *
 * Workflow:
 *   1. admit()     → creates admission, assigns bed, creates encounter
 *   2. transferBed() → moves patient to different bed
 *   3. addTreatment() → creates treatment order
 *   4. addMedication() → creates IPD medication record
 *   5. discharge() → closes admission, releases bed, updates visit
 *
 * ALL operations are transactional.
 * NO existing code is modified — this is a new service.
 */
class AdmissionService
{
    // ══════════════════════════════════════════════════════════════════════════
    // QUERIES
    // ══════════════════════════════════════════════════════════════════════════

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return AdmissionModel::query()
            ->with(['patient', 'visit', 'ward', 'room', 'bed'])
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('code', 'like', "%{$s}%")
                  ->orWhereHas('patient', fn($p) =>
                      $p->where('surname', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                  )
            )
            ->when($filters['status'] ?? null,  fn($q, $v) => $q->where('status', $v))
            ->when($filters['ward_id'] ?? null, fn($q, $v) => $q->where('ward_id', $v))
            ->when($filters['date'] ?? null,    fn($q, $v) => $q->whereDate('admitted_at', $v))
            ->latest('admitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByCode(string $code): AdmissionModel
    {
        return AdmissionModel::where('code', $code)
            ->with(['patient', 'visit', 'ward', 'room', 'bed', 'treatments', 'inpatientMedications'])
            ->firstOrFail();
    }

    public function activeAdmissions(): \Illuminate\Database\Eloquent\Collection
    {
        return AdmissionModel::active()
            ->with(['patient', 'ward', 'bed'])
            ->orderBy('admitted_at')
            ->get();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 1. ADMIT — creates admission + assigns bed + creates encounter
    // ══════════════════════════════════════════════════════════════════════════

    public function admit(array $data): AdmissionModel
    {
        return DB::transaction(function () use ($data) {
            $visit = VisitModel::where('code', $data['visit_code'])->firstOrFail();

            // Validate: visit must be IPD
            if ($visit->visit_type !== 'IPD') {
                throw new \RuntimeException('Cannot admit OPD visit. Visit must be IPD type.');
            }

            // Validate: no active admission for this visit
            $existing = AdmissionModel::where('visit_code', $visit->code)->active()->first();
            if ($existing) {
                throw new \RuntimeException("Visit {$visit->code} already has active admission {$existing->code}.");
            }

            // Validate: bed must be available
            $bed = null;
            if (!empty($data['bed_id'])) {
                $bed = BedModel::findOrFail($data['bed_id']);
                if (!$bed->isAvailable()) {
                    throw new \RuntimeException("Bed {$bed->name} is not available (status: {$bed->status}).");
                }
            }

            $admCode = ClinicCodeService::next(currentClinic()->id, 'ADM');

            // Create admission record
            $admission = AdmissionModel::create([
                'code'                  => $admCode,
                'patient_code'          => $visit->patient_code,
                'visit_code'            => $visit->code,
                'admission_type'        => $data['admission_type'] ?? $visit->admission_type,
                'admission_reason'      => $data['admission_reason'] ?? null,
                'admission_notes'       => $data['admission_notes'] ?? null,
                'ward_id'               => $data['ward_id'] ?? $bed?->ward_id,
                'room_id'               => $data['room_id'] ?? $bed?->room_id,
                'bed_id'                => $data['bed_id'] ?? null,
                'attending_doctor'      => $data['attending_doctor'] ?? null,
                'admitting_doctor'      => $data['admitting_doctor'] ?? null,
                'primary_nurse'         => $data['primary_nurse'] ?? null,
                'admitted_at'           => $data['admitted_at'] ?? now(),
                'expected_discharge_at' => $data['expected_discharge_at'] ?? null,
                'status'                => AdmissionModel::STATUS_ADMITTED,
            ]);

            // Assign bed (uses existing BedModel::assignTo)
            if ($bed) {
                $bed->assignTo($visit->code, $visit->patient_code);
            }

            // Create/update encounter for clinical data linkage
            $encounterCode = "IPD-{$visit->code}";
            OutInPatientModel::updateOrCreate(
                ['code' => $encounterCode],
                [
                    'visit_code'     => $visit->code,
                    'visit_type'     => 'IPD',
                    'admission_code' => $admCode,
                    'ward_id'        => $admission->ward_id,
                    'room_id'        => $admission->room_id,
                    'bed_id'         => $admission->bed_id,
                    'name'           => $admission->ward?->name,
                    'bed'            => $bed?->name,
                    'started_at'     => $admission->admitted_at,
                    'status'         => 'active',
                    'title'          => "IPD — {$visit->surname}, {$visit->name}",
                ]
            );

            // Update visit admission_status
            $visit->update(['admission_status' => 'admitted']);

            // Link encounter back to admission
            $admission->update(['encounter_code' => $encounterCode]);

            return $admission;
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 2. TRANSFER BED — move patient to a different bed
    // ══════════════════════════════════════════════════════════════════════════

    public function transferBed(string $admissionCode, int $newBedId): AdmissionModel
    {
        return DB::transaction(function () use ($admissionCode, $newBedId) {
            $admission = AdmissionModel::where('code', $admissionCode)->firstOrFail();

            if (!$admission->isAdmitted()) {
                throw new \RuntimeException('Cannot transfer bed — admission is not active.');
            }

            $newBed = BedModel::findOrFail($newBedId);
            if (!$newBed->isAvailable()) {
                throw new \RuntimeException("Bed {$newBed->name} is not available.");
            }

            // Release old bed
            if ($admission->bed_id) {
                $oldBed = BedModel::find($admission->bed_id);
                $oldBed?->release();
            }

            // Assign new bed
            $newBed->assignTo($admission->visit_code, $admission->patient_code);

            // Update admission
            $admission->update([
                'ward_id' => $newBed->ward_id,
                'room_id' => $newBed->room_id,
                'bed_id'  => $newBed->id,
            ]);

            return $admission->fresh(['ward', 'room', 'bed']);
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 3. ADD TREATMENT
    // ══════════════════════════════════════════════════════════════════════════

    public function addTreatment(string $admissionCode, array $data): TreatmentModel
    {
        $admission = AdmissionModel::where('code', $admissionCode)->firstOrFail();

        if (!$admission->isAdmitted()) {
            throw new \RuntimeException('Cannot add treatment — admission is not active.');
        }

        return TreatmentModel::create([
            'code'           => ClinicCodeService::next(currentClinic()->id, 'TRT'),
            'admission_code' => $admission->code,
            'visit_code'     => $admission->visit_code,
            'patient_code'   => $admission->patient_code,
            'treatment_type' => $data['treatment_type'],
            'name'           => $data['name'],
            'instructions'   => $data['instructions'] ?? null,
            'frequency'      => $data['frequency'] ?? null,
            'route'          => $data['route'] ?? null,
            'duration'       => $data['duration'] ?? null,
            'ordered_by'     => $data['ordered_by'] ?? auth()->user()?->name,
            'ordered_at'     => now(),
            'status'         => 'ordered',
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 4. ADD INPATIENT MEDICATION
    // ══════════════════════════════════════════════════════════════════════════

    public function addMedication(string $admissionCode, array $data): InpatientMedicationModel
    {
        $admission = AdmissionModel::where('code', $admissionCode)->firstOrFail();

        if (!$admission->isAdmitted()) {
            throw new \RuntimeException('Cannot add medication — admission is not active.');
        }

        $medicine = \App\Models\MedicineModel::findOrFail($data['medicine_id']);

        return InpatientMedicationModel::create([
            'code'            => ClinicCodeService::next(currentClinic()->id, 'IPM'),
            'admission_code'  => $admission->code,
            'visit_code'      => $admission->visit_code,
            'patient_code'    => $admission->patient_code,
            'medicine_id'     => $medicine->id,
            'medicine_name'   => $medicine->name,
            'dosage'          => $data['dosage'] ?? null,
            'route'           => $data['route'] ?? null,
            'frequency'       => $data['frequency'] ?? null,
            'quantity'        => $data['quantity'] ?? 1,
            'start_date'      => $data['start_date'] ?? now(),
            'end_date'        => $data['end_date'] ?? null,
            'prescribed_by'   => $data['prescribed_by'] ?? auth()->user()?->name,
            'status'          => 'active',
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 5. DISCHARGE — closes admission, releases bed, updates visit
    // ══════════════════════════════════════════════════════════════════════════

    public function discharge(string $admissionCode, array $data): AdmissionModel
    {
        return DB::transaction(function () use ($admissionCode, $data) {
            $admission = AdmissionModel::where('code', $admissionCode)->firstOrFail();

            if (!$admission->isAdmitted()) {
                throw new \RuntimeException("Cannot discharge — admission status is '{$admission->status}'.");
            }

            // 1. Update admission record
            $admission->update([
                'status'              => AdmissionModel::STATUS_DISCHARGED,
                'discharged_at'       => $data['discharged_at'] ?? now(),
                'discharge_type'      => $data['discharge_type'] ?? 'Normal',
                'discharge_summary'   => $data['discharge_summary'] ?? null,
                'discharge_condition' => $data['discharge_condition'] ?? null,
                'discharged_by'       => $data['discharged_by'] ?? auth()->user()?->name,
            ]);

            // 2. Release bed (sets status → cleaning, clears visit/patient pointers)
            if ($admission->bed_id) {
                $bed = BedModel::find($admission->bed_id);
                $bed?->release();
            }

            // 3. Close encounter
            $encounter = OutInPatientModel::where('admission_code', $admissionCode)->first();
            $encounter?->update([
                'ended_at' => $admission->discharged_at,
                'status'   => 'completed',
            ]);

            // 4. Mark active treatments as completed
            TreatmentModel::where('admission_code', $admissionCode)
                ->whereIn('status', ['ordered', 'in_progress'])
                ->update(['status' => 'completed']);

            // 5. Mark active IPD medications as completed
            InpatientMedicationModel::where('admission_code', $admissionCode)
                ->where('status', 'active')
                ->update([
                    'status'   => 'completed',
                    'end_date' => now(),
                ]);

            // 6. Update parent visit
            $visit = VisitModel::where('code', $admission->visit_code)->first();
            $visit?->update([
                'discharged_at'    => $admission->discharged_at,
                'discharge_type'   => $admission->discharge_type,
                'visit_outcome'    => $data['visit_outcome'] ?? null,
                'admission_status' => 'discharged',
            ]);

            return $admission->fresh(['patient', 'ward', 'bed']);
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATS
    // ══════════════════════════════════════════════════════════════════════════

    public function stats(): array
    {
        return [
            'active_admissions'   => AdmissionModel::active()->count(),
            'today_admissions'    => AdmissionModel::whereDate('admitted_at', today())->count(),
            'today_discharges'    => AdmissionModel::whereDate('discharged_at', today())->count(),
            'avg_length_of_stay'  => (int) round(
                AdmissionModel::discharged()
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (discharged_at - admitted_at)) / 86400) as avg_days')
                    ->value('avg_days') ?? 0
            ),
        ];
    }
}
