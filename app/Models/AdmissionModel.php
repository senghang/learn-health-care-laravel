<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AdmissionModel — IPD admission lifecycle.
 *
 * Status machine:
 *   admitted → discharged     (normal discharge)
 *   admitted → transferred    (transfer to another facility)
 *   admitted → deceased       (patient death)
 *   admitted → cancelled      (admission error)
 *
 * Relationships (per ERD):
 *   Patient → Visit(IPD) → Admission → Ward → Room → Bed
 *   Admission → many Treatments
 *   Admission → many InpatientMedications
 *   Admission → many VitalSigns (via encounter_code)
 */
class AdmissionModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope, HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\AdmissionFactory::new();
    }

    protected $table = 'admissions';

    protected $fillable = [
        'clinic_id', 'code', 'patient_code', 'visit_code', 'encounter_code',
        'admission_type', 'admission_reason', 'admission_notes',
        'ward_id', 'room_id', 'bed_id',
        'attending_doctor', 'admitting_doctor', 'primary_nurse',
        'admitted_at', 'discharged_at', 'expected_discharge_at',
        'discharge_type', 'discharge_summary', 'discharge_condition', 'discharged_by',
        'status',
    ];

    protected $casts = [
        'admitted_at'            => 'datetime',
        'discharged_at'          => 'datetime',
        'expected_discharge_at'  => 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_ADMITTED    = 'admitted';
    public const STATUS_DISCHARGED  = 'discharged';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_DECEASED    = 'deceased';
    public const STATUS_CANCELLED   = 'cancelled';

    public const ACTIVE_STATUSES = [self::STATUS_ADMITTED];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(WardModel::class, 'ward_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(BedModel::class, 'bed_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(TreatmentModel::class, 'admission_code', 'code');
    }

    public function inpatientMedications(): HasMany
    {
        return $this->hasMany(InpatientMedicationModel::class, 'admission_code', 'code');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeDischarged($query)
    {
        return $query->where('status', self::STATUS_DISCHARGED);
    }

    // ── Status checks ─────────────────────────────────────────────────────────

    public function isAdmitted(): bool   { return $this->status === self::STATUS_ADMITTED; }
    public function isDischarged(): bool  { return $this->status === self::STATUS_DISCHARGED; }
    public function isActive(): bool     { return in_array($this->status, self::ACTIVE_STATUSES); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getLengthOfStayAttribute(): ?int
    {
        if (!$this->admitted_at) return null;
        $end = $this->discharged_at ?? now();
        return $this->admitted_at->diffInDays($end);
    }
}
