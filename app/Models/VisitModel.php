<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VisitModel — OPD/IPD visit header.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added 'priority', 'clinical_summary' to $fillable
 *   ✅ All existing fillable/casts/relationships preserved
 *   ✅ Zero breaking changes
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class VisitModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'visits';

    protected $fillable = [
        'clinic_id', 'code', 'health_facility_code',
        'patient_code', 'surname', 'name', 'given_name',
        'visit_type', 'priority',                            // ← NEW
        'admission_type', 'admission_status',
        'discharge_type', 'visit_outcome', 'clinical_summary', // ← NEW
        'reason_for_visit', 'attending_doctor', 'ward_id',
        'admitted_at', 'discharged_at', 'followup_at',
        'done_steps', 'skipped_steps',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'admitted_at'   => 'datetime',
        'discharged_at' => 'datetime',
        'followup_at'   => 'datetime',
        'done_steps'    => 'array',
        'skipped_steps' => 'array',
    ];

    // ── Relationships (OPD workflow) ──────────────────────────────────────────

    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function triages(): HasMany { return $this->hasMany(TriageModel::class, 'visit_code', 'code'); }
    public function medicalHistories(): HasMany { return $this->hasMany(MedicalHistoryModel::class, 'visit_code', 'code'); }
    public function physicalExaminations(): HasMany { return $this->hasMany(PhysicalExaminationModel::class, 'visit_code', 'code'); }
    public function diagnoses(): HasMany { return $this->hasMany(DiagnosisModel::class, 'visit_code', 'code'); }
    public function prescriptions(): HasMany { return $this->hasMany(PrescriptionModel::class, 'visit_code', 'code'); }
    public function laboratories(): HasMany { return $this->hasMany(LaboratoryModel::class, 'visit_code', 'code'); }
    public function imageries(): HasMany { return $this->hasMany(ImageryModel::class, 'visit_code', 'code'); }
    public function referrals(): HasMany { return $this->hasMany(ReferralModel::class, 'visit_code', 'code'); }
    public function invoices(): HasMany { return $this->hasMany(InvoiceModel::class, 'visit_code', 'code'); }

    // ── Relationships (IPD workflow) ──────────────────────────────────────────

    public function admissions(): HasMany { return $this->hasMany(AdmissionModel::class, 'visit_code', 'code'); }
    public function admission(): HasOne { return $this->hasOne(AdmissionModel::class, 'visit_code', 'code')->latestOfMany('admitted_at'); }

    // ── Encounters ────────────────────────────────────────────────────────────

    public function encounters(): HasMany { return $this->hasMany(OutInPatientModel::class, 'visit_code', 'code'); }
    public function encounter(): HasOne { return $this->hasOne(OutInPatientModel::class, 'visit_code', 'code')->latestOfMany('started_at'); }
    public function soap(): HasOne { return $this->hasOne(SoapModel::class, 'encounter_code', 'code'); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getGivenNameAttribute(): string { return $this->attributes['given_name'] ?? $this->name ?? ''; }
    public function getFullNameAttribute(): string { return trim("{$this->surname} {$this->given_name}"); }
    public function isIPD(): bool { return $this->visit_type === 'IPD'; }
    public function isOPD(): bool { return $this->visit_type === 'OPD'; }
    public function isActive(): bool { return is_null($this->discharged_at); }

    public function getProgressPercentAttribute(): int
    {
        $total = 10;
        $done = count($this->done_steps ?? []);
        $skipped = count($this->skipped_steps ?? []);
        return $total > 0 ? (int) round(($done + $skipped) / $total * 100) : 0;
    }

    public function getStepsDoneAttribute(): int
    {
        return count($this->done_steps ?? []);
    }

    /**
     * NEW: Priority color helper for blade views.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'Emergency' => '#e74c3c',
            'Urgent'    => '#ff771d',
            'Standard'  => '#3498db',
            'Low'       => '#95a5a6',
            default     => '#ccc',
        };
    }
}
