<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ImageryModel — imaging/radiology order header.
 *
 * Workflow: requested → completed
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE: Added status helpers + computed attributes. All existing preserved.
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class ImageryModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'imageries';

    protected $fillable = [
        'clinic_id', 'code', 'patient_code', 'visit_code', 'encounter_code',
        'category', 'status', 'urgency',
        'requested_at', 'requested_by', 'title',
        'collected_at', 'collected_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public const CATEGORIES = ['X-ray', 'Ultrasound', 'CT', 'MRI', 'ECG', 'Endoscopy'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
    public function results(): HasMany { return $this->hasMany(ImageryResultModel::class, 'request_code', 'code'); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'requested' => '#ff771d',
            'completed' => '#2eca6a',
            'cancelled' => '#95a5a6',
            default     => '#aaa',
        };
    }

    public function isPending(): bool { return $this->status === 'requested'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function scopePending($q) { return $q->where('status', 'requested'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
}
