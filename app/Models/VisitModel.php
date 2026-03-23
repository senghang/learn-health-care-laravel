<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\MedicalHistoryModel;
use App\Models\PhysicalExaminationModel;
use App\Models\OutInPatientModel;
use App\Models\SoapModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VisitModel — now includes ClinicScope for proper multi-tenant isolation.
 *
 * FIX: Previously missing ClinicScope, which meant:
 *   - VisitModel::all() returned visits from ALL clinics
 *   - Cross-clinic data leakage was possible
 *   - Composite unique code (clinic_id, code) was not enforced
 */
class VisitModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope; // ← ClinicScope ADDED

    protected $table = 'visits';

    protected $fillable = [
        'clinic_id',          // ← Now explicitly fillable
        'code',
        'health_facility_code',
        'patient_code',
        'surname',
        'name',
        'visit_type',
        'admission_type',
        'discharge_type',
        'visit_outcome',
        'admitted_at',
        'discharged_at',
        'followup_at',
        'done_steps',
        'skipped_steps',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'admitted_at'   => 'datetime',
        'discharged_at' => 'datetime',
        'followup_at'   => 'datetime',
        'done_steps'    => 'array',    // JSONB → PHP array automatically
        'skipped_steps' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function triages(): HasMany
    {
        return $this->hasMany(TriageModel::class, 'visit_code', 'code');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(DiagnosisModel::class, 'visit_code', 'code');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PrescriptionModel::class, 'visit_code', 'code');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'visit_code', 'code');
    }

    public function laboratories(): HasMany
    {
        return $this->hasMany(LaboratoryModel::class, 'visit_code', 'code');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(ReferralModel::class, 'visit_code', 'code');
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(OutInPatientModel::class, 'visit_code', 'code');
    }

    public function encounter(): HasOne
    {
        return $this->hasOne(OutInPatientModel::class, 'visit_code', 'code')
            ->latestOfMany('started_at');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function medicalHistories(): HasMany
    {
        return $this->hasMany(MedicalHistoryModel::class, 'visit_code', 'code');
    }

    public function physicalExaminations(): HasMany
    {
        return $this->hasMany(PhysicalExaminationModel::class, 'visit_code', 'code');
    }

    public function soap(): HasOne
    {
        return $this->hasOne(SoapModel::class, 'encounter_code', 'code');
    }

        public function getGivenNameAttribute(): string
    {
        return $this->name ?? '';
    }

    public function getPatientNameAttribute(): string
    {
        return "{$this->surname}, {$this->name}";
    }

    public function getStepsDoneAttribute(): int
    {
        return count($this->done_steps ?? []);
    }

    public function getStepsSkippedAttribute(): int
    {
        return count($this->skipped_steps ?? []);
    }

    public function getProgressPercentAttribute(): int
    {
        $total = 10; // update if step count changes
        return (int) round($this->steps_done / $total * 100);
    }

    public function isStepDone(string $id): bool
    {
        return in_array($id, $this->done_steps ?? []);
    }

    public function isStepSkipped(string $id): bool
    {
        return in_array($id, $this->skipped_steps ?? []);
    }
}
