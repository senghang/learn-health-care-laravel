<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\PrescriptionMedicationModel;
use App\Common\Utils\CodeGenerator;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PrescriptionStep
 *
 * Blade field names → DB column names:
 *   prescribed_by            → prescriptions.prescribed_by
 *   prescribed_at            → prescriptions.prescribed_at
 *   meds[N][medicine_name]   → prescription_medications.medicine_name
 *   meds[N][strength]        → .strength
 *   meds[N][form]            → .form
 *   meds[N][method]          → .method
 *   meds[N][note]            → .note
 *   meds[N][morning]         → .morning
 *   meds[N][afternoon]       → .afternoon
 *   meds[N][evening]         → .evening
 *   meds[N][night]           → .night
 *   meds[N][days]            → .days
 *   meds[N][interval]        → .interval
 *
 * Save strategy: updateOrCreate the prescription header keyed on visit_code,
 * then delete all old medication rows and re-insert fresh ones.
 * This prevents duplicate prescriptions on re-submit.
 */
class PrescriptionStep extends AbstractWorkflowStep
{
    public function id(): string          { return 'prescription'; }
    public function labelKm(): string     { return 'ថ្នាំ'; }
    public function labelEn(): string     { return 'Prescription'; }
    public function icon(): string        { return '💊'; }
    public function color(): string       { return '#e91e8c'; }
    public function description(): string { return 'ថ្នាំ + កាលវិភាគព្រឹក/ថ្ងៃ/ល្ងាច/យប់'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'prescribed_by'        => 'nullable|string|max:120',
            'prescribed_at'        => 'nullable|date',
            'meds'                 => 'required|array|min:1',
            'meds.*.medicine_name' => 'required|string|max:200',
            'meds.*.strength'      => 'nullable|string|max:60',
            'meds.*.form'          => 'nullable|string|max:60',
            'meds.*.method'        => 'nullable|string|max:80',
            'meds.*.note'          => 'nullable|string',
            'meds.*.morning'       => 'nullable|numeric|min:0',
            'meds.*.afternoon'     => 'nullable|numeric|min:0',
            'meds.*.evening'       => 'nullable|numeric|min:0',
            'meds.*.night'         => 'nullable|numeric|min:0',
            'meds.*.days'          => 'nullable|integer|min:1',
            'meds.*.interval'      => 'nullable|string|max:40',
        ]);

        // Filter out blank rows the user may have left empty
        $meds = array_filter(
            $data['meds'],
            fn($m) => !empty(trim($m['medicine_name'] ?? ''))
        );

        if (empty($meds)) {
            return; // nothing to save
        }

        DB::transaction(function () use ($visit, $data, $meds) {

            // ── 1. Upsert prescription header (one per visit) ─────────────────
            $rx = PrescriptionModel::updateOrCreate(
                ['visit_code' => $visit->code],
                [
                    'code'          => CodeGenerator::prescription($visit->code),
                    'patient_code'  => $visit->patient_code,
                    'prescribed_at' => $data['prescribed_at'] ?? now(),
                    'prescribed_by' => $data['prescribed_by'] ?? null,
                    'title'         => implode(', ', array_column($meds, 'medicine_name')),
                ]
            );

            // ── 2. Replace all medication rows cleanly ────────────────────────
            // Hard-delete old rows (no soft-delete issue with unique indexes)
            PrescriptionMedicationModel::where('prescription_code', $rx->code)->forceDelete();

            // ── 3. Insert fresh medication rows ───────────────────────────────
            foreach ($meds as $med) {
                PrescriptionMedicationModel::create([
                    'prescription_code' => $rx->code,
                    'medicine_name'     => $med['medicine_name'],
                    'strength'          => $med['strength']   ?? null,
                    'form'              => $med['form']        ?? null,
                    'method'            => $med['method']      ?? null,
                    'note'              => $med['note']        ?? null,
                    'morning'           => $med['morning']     ?? 0,
                    'afternoon'         => $med['afternoon']   ?? 0,
                    'evening'           => $med['evening']     ?? 0,
                    'night'             => $med['night']       ?? 0,
                    'days'              => $med['days']        ?? null,
                    'interval'          => $med['interval']    ?? null,
                ]);
            }
        });
    }

    public function viewData(VisitModel $visit): array
    {
        // Load the single prescription for this visit with its medications
        $rx = PrescriptionModel::where('visit_code', $visit->code)
            ->with('medications')
            ->latest()
            ->first();

        return [
            'prescription' => $rx,
            'medications'  => $rx?->medications ?? collect([]),
        ];
    }
}
