<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ImageryResultModel — imaging result with optional image attachments.
 *
 * No changes from existing — already complete.
 */
class ImageryResultModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'imagery_results';

    protected $fillable = [
        'request_code', 'name', 'category',
        'images', 'result', 'conclusion', 'value_type',
        'verified_at', 'verified_by', 'recorded_at', 'recorded_by',
    ];

    protected $casts = [
        'images'      => 'array',
        'verified_at' => 'datetime',
        'recorded_at' => 'datetime',
    ];

    public function imagery(): BelongsTo
    {
        return $this->belongsTo(ImageryModel::class, 'request_code', 'code');
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verified_at !== null;
    }
}
