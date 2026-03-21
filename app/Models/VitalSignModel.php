<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSignModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'vital_signs';

    protected $fillable = [
        'code',
        'visit_code',       // ← was missing; needed for direct create() calls
        'encounter_code',
        'patient_code',
        'recorded_at',
        'recorded_by',
        'title',
    ];

    protected $casts = ['recorded_at' => 'datetime'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(VitalSignObservationModel::class, 'vital_sign_code', 'code');
    }
}
