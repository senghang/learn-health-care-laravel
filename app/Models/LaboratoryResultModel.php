<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * LaboratoryResultModel — individual test result within a lab order.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added 'flag' to $fillable (H|L|N|Critical)
 *   ✅ Added isAbnormal/isCritical computed helpers
 *   ✅ All existing fillable preserved
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class LaboratoryResultModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'laboratory_results';

    protected $fillable = [
        'request_code', 'name', 'category',
        'value', 'value_type', 'value_unit',
        'reference_range', 'interpretation', 'flag',
        'verified_at', 'verified_by',
        'recorded_at', 'recorded_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'recorded_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(LaboratoryModel::class, 'request_code', 'code');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getIsAbnormalAttribute(): bool
    {
        $abnormal = ['H', 'L', 'High', 'Low', 'Positive', 'Critical'];
        return in_array($this->flag, $abnormal) || in_array($this->interpretation, $abnormal);
    }

    public function getIsCriticalAttribute(): bool
    {
        return $this->flag === 'Critical'
            || strtolower($this->interpretation ?? '') === 'critical';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verified_at !== null;
    }

    public function getFlagColorAttribute(): string
    {
        return match($this->flag) {
            'H', 'High'     => '#e74c3c',
            'L', 'Low'      => '#3498db',
            'Critical'       => '#e74c3c',
            'N', 'Normal'   => '#2eca6a',
            default          => '#aaa',
        };
    }
}
