<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * LaboratoryModel — lab order header.
 *
 * Workflow: requested → collected → processing → completed
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added 'category' to $fillable
 *   ✅ Added status constants + helper methods
 *   ✅ Added computed attributes for UI
 *   ✅ All existing fillable/relationships preserved
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class LaboratoryModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'laboratories';

    protected $fillable = [
        'clinic_id', 'code', 'patient_code', 'visit_code', 'encounter_code',
        'category',
        'requested_at', 'requested_by', 'title',
        'status', 'urgency',
        'collected_at', 'collected_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_REQUESTED  = 'requested';
    public const STATUS_COLLECTED  = 'collected';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_CANCELLED  = 'cancelled';

    public const URGENCY_NORMAL = 'normal';
    public const URGENCY_URGENT = 'urgent';
    public const URGENCY_STAT   = 'stat';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
    public function results(): HasMany { return $this->hasMany(LaboratoryResultModel::class, 'request_code', 'code'); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'requested'  => '#ff771d',
            'collected'  => '#9b59b6',
            'processing' => '#3498db',
            'completed'  => '#2eca6a',
            'cancelled'  => '#95a5a6',
            default      => '#aaa',
        };
    }

    public function getUrgencyColorAttribute(): string
    {
        return match($this->urgency) {
            'stat'   => '#e74c3c',
            'urgent' => '#ff771d',
            default  => '#3498db',
        };
    }

    public function getResultsCountAttribute(): int
    {
        return $this->results()->count();
    }

    public function getCompletedResultsCountAttribute(): int
    {
        return $this->results()->whereNotNull('value')->count();
    }

    public function getHasCriticalAttribute(): bool
    {
        return $this->results()
            ->where(function ($q) {
                $q->where('flag', 'Critical')
                  ->orWhere('interpretation', 'Critical')
                  ->orWhereRaw("LOWER(interpretation) IN ('high', 'low', 'critical', 'positive')");
            })
            ->exists();
    }

    public function isPending(): bool { return in_array($this->status, ['requested', 'collected', 'processing']); }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($q) { return $q->whereIn('status', ['requested', 'collected', 'processing']); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeUrgent($q) { return $q->whereIn('urgency', ['urgent', 'stat']); }
}
