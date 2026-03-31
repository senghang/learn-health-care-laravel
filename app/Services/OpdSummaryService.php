<?php

namespace App\Services;

use App\Models\VisitModel;
use Illuminate\Support\Collection;

/**
 * OpdSummaryService — aggregates all clinical data for a single visit.
 *
 * Used by:
 *   - Visit show page (full clinical summary)
 *   - Discharge documentation
 *   - Print templates
 *
 * This is a NEW service. Does NOT modify any existing service.
 * Read-only — performs no writes.
 */
class OpdSummaryService
{
    /**
     * Load a visit with ALL clinical relations in one call.
     * Uses eager loading to avoid N+1 queries.
     *
     * @param string $code Visit code
     * @return VisitModel Fully loaded visit
     */
    public function loadFull(string $code): VisitModel
    {
        return VisitModel::where('code', $code)
            ->with([
                'patient.address',
                'patient.identifications',
                'patient.contacts',
                'triages',
                'encounters.vitalSigns.observations',
                'encounters.soap',
                'medicalHistories',
                'physicalExaminations',
                'diagnoses',
                'prescriptions.medications',
                'laboratories.results',
                'imageries',
                'referrals',
                'invoices.services',
                'invoices.medications',
                'invoices.payments',
            ])
            ->firstOrFail();
    }

    /**
     * Build a structured clinical summary for the visit.
     * Returns a plain array that blade views or API can consume.
     */
    public function summary(VisitModel $visit): array
    {
        // Ensure all relations are loaded
        $visit->loadMissing([
            'patient', 'triages',
            'encounters.vitalSigns.observations', 'encounters.soap',
            'medicalHistories', 'physicalExaminations',
            'diagnoses', 'prescriptions.medications',
            'laboratories.results', 'imageries',
            'referrals', 'invoices.payments',
        ]);

        $encounter = $visit->encounters->first();
        $triage    = $visit->triages->first();
        $soap      = $encounter ? $encounter->soap : null;

        // Flatten vital signs observations
        $latestVitals = $this->latestVitals($encounter);

        return [
            // ── Visit Header ──────────────────────────────────────────────
            'visit'   => [
                'code'           => $visit->code,
                'type'           => $visit->visit_type,
                'priority'       => $visit->priority,
                'admission_type' => $visit->admission_type,
                'admitted_at'    => $visit->admitted_at,
                'discharged_at'  => $visit->discharged_at,
                'is_active'      => $visit->isActive(),
                'progress'       => $visit->progress_percent,
                'steps_done'     => $visit->done_steps ?? [],
                'steps_skipped'  => $visit->skipped_steps ?? [],
            ],

            // ── Patient ───────────────────────────────────────────────────
            'patient' => $visit->patient,

            // ── Triage ────────────────────────────────────────────────────
            'triage' => $triage ? [
                'chief_complaint' => $triage->chief_complaint,
                'triage_level'    => $triage->triage_level ?? null,
                'height'          => $triage->height,
                'weight'          => $triage->weight,
                'bmi'             => $triage->bmi ?? $this->calcBmi($triage->height, $triage->weight),
                'recorded_at'     => $triage->recorded_at,
                'recorded_by'     => $triage->recorded_by,
            ] : null,

            // ── Vital Signs (latest set) ──────────────────────────────────
            'vitals' => $latestVitals,

            // ── Medical History ────────────────────────────────────────────
            'histories' => $visit->medicalHistories->map(fn($h) => [
                'name'  => $h->name,
                'value' => $h->value,
            ]),

            // ── Physical Examination ──────────────────────────────────────
            'examinations' => $visit->physicalExaminations->map(fn($pe) => [
                'name'  => $pe->name,
                'value' => $pe->value,
            ]),

            // ── SOAP Notes ────────────────────────────────────────────────
            'soap' => $soap ? [
                'subjective' => $soap->subjective,
                'objective'  => $soap->objective,
                'assessment' => $soap->assessment,
                'evaluation' => $soap->evaluation,
                'plan'       => $soap->plan,
            ] : null,

            // ── Diagnoses ─────────────────────────────────────────────────
            'diagnoses' => $visit->diagnoses->map(fn($d) => [
                'type'        => $d->diagnosis_type,
                'code'        => $d->diagnosis_code,
                'name'        => $d->diagnosis_name,
                'description' => $d->diagnosis_description,
                'diagnosed_by'=> $d->diagnosed_by,
            ]),

            // ── Prescriptions ─────────────────────────────────────────────
            'prescriptions' => $visit->prescriptions->map(fn($rx) => [
                'code'          => $rx->code,
                'prescribed_by' => $rx->prescribed_by,
                'prescribed_at' => $rx->prescribed_at,
                'status'        => $rx->dispensed_status,
                'medications'   => $rx->medications->map(fn($m) => [
                    'name'     => $m->medicine_name,
                    'strength' => $m->strength,
                    'form'     => $m->form,
                    'morning'  => $m->morning,
                    'afternoon'=> $m->afternoon,
                    'evening'  => $m->evening,
                    'night'    => $m->night,
                    'days'     => $m->days,
                    'note'     => $m->note,
                ]),
            ]),

            // ── Lab Results ───────────────────────────────────────────────
            'labs' => $visit->laboratories->map(fn($lab) => [
                'code'         => $lab->code,
                'category'     => $lab->category,
                'requested_at' => $lab->requested_at,
                'requested_by' => $lab->requested_by,
                'results'      => $lab->results->map(fn($r) => [
                    'name'   => $r->name,
                    'result' => $r->result,
                    'unit'   => $r->unit,
                    'range'  => $r->reference_range,
                    'flag'   => $r->flag,
                ]),
            ]),

            // ── Imaging ───────────────────────────────────────────────────
            'imaging' => $visit->imageries->map(fn($img) => [
                'code'     => $img->code,
                'category' => $img->category,
                'title'    => $img->title,
            ]),

            // ── Referrals ─────────────────────────────────────────────────
            'referrals' => $visit->referrals,

            // ── Billing ───────────────────────────────────────────────────
            'invoices' => $visit->invoices->map(fn($inv) => [
                'code'         => $inv->code,
                'total'        => $inv->total,
                'paid'         => $inv->paid_amount,
                'balance'      => $inv->balance,
                'status'       => $inv->status,
                'payment_type' => $inv->payment_type,
            ]),
        ];
    }

    /**
     * Get the latest vital signs as a flat key→value array.
     */
    private function latestVitals($encounter): array
    {
        if (!$encounter) return [];

        $latest = $encounter->vitalSigns->sortByDesc('recorded_at')->first();
        if (!$latest) return [];

        return $latest->observations->pluck('value', 'name')->merge([
            'recorded_at' => $latest->recorded_at,
            'recorded_by' => $latest->recorded_by,
        ])->toArray();
    }

    /**
     * Calculate BMI from height (cm) and weight (kg).
     */
    private function calcBmi(?float $height, ?float $weight): ?float
    {
        if (!$height || !$weight || $height <= 0) return null;
        $heightM = $height / 100;
        return round($weight / ($heightM * $heightM), 1);
    }
}
