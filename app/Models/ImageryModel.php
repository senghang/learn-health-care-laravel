<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageryModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'imageries';

    protected $fillable = [
        'code', 'patient_code', 'visit_code', 'encounter_code',
        'category', 'requested_at', 'requested_by', 'title',
        'collected_at', 'collected_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ImageryResultModel::class, 'request_code', 'code');
    }
}
