<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintTemplateModel extends Model
{
    use SoftDeletes, Auditable;

    public const TYPES = [
        'prescription',
        'invoice',
        'referral_letter',
        'discharge_summary',
        'lab_result',
    ];
    protected $table = 'print_templates';
    protected $fillable = [
        'clinic_id', 'code', 'type', 'name', 'locale',
        'content', 'is_default', 'is_active',
        'paper_size', 'orientation',
    ];
    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    /**
     * Find the best template for a clinic / type / locale.
     * Falls back: exact locale → is_default → first active.
     */
    public static function resolve(int $clinicId, string $type, string $locale = 'km'): ?self
    {
        return static::where('clinic_id', $clinicId)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN locale = ? THEN 0 WHEN is_default = true THEN 1 ELSE 2 END", [$locale])
            ->first();
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }
}
