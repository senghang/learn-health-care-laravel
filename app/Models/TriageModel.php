<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TriageModel — chief complaint + initial measurements.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added 'triage_level', 'bmi' to $fillable
 *   ✅ Added getBmiAttribute() auto-calculator
 *   ✅ All existing fields/relationships preserved
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class TriageModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'triages';

    protected $fillable = [
        'code',
        'patient_code',
        'visit_code',
        'encounter_code',
        'chief_complaint',
        'triage_level',      // ← NEW: Emergency|Urgent|Standard|Low
        'height',
        'weight',
        'bmi',               // ← NEW: auto-calculated
        'recorded_at',
        'recorded_by',
        'title',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'height'      => 'decimal:1',
        'weight'      => 'decimal:2',
        'bmi'         => 'decimal:1',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSignModel::class, 'encounter_code', 'encounter_code');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    /**
     * Auto-calculate BMI from height (cm) and weight (kg).
     * Returns stored value if exists, otherwise calculates on the fly.
     */
    public function getCalculatedBmiAttribute(): ?float
    {
        if ($this->attributes['bmi'] ?? null) {
            return (float) $this->attributes['bmi'];
        }

        if (!$this->height || !$this->weight || $this->height <= 0) {
            return null;
        }

        $heightM = $this->height / 100;
        return round($this->weight / ($heightM * $heightM), 1);
    }

    /**
     * BMI category for display.
     */
    public function getBmiCategoryAttribute(): ?string
    {
        $bmi = $this->calculated_bmi;
        if ($bmi === null) return null;

        return match(true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            default     => 'Obese',
        };
    }

    /**
     * Triage level color for UI badges.
     */
    public function getTriageLevelColorAttribute(): string
    {
        return match($this->triage_level) {
            'Emergency' => '#e74c3c',
            'Urgent'    => '#ff771d',
            'Standard'  => '#3498db',
            'Low'       => '#95a5a6',
            default     => '#ccc',
        };
    }
}
