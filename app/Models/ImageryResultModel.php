<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
