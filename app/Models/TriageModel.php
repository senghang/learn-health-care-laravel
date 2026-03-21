<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'height',
        'weight',
        'recorded_at',
        'recorded_by',
        'title',
    ];

    protected $casts = ['recorded_at' => 'datetime', 'height' => 'decimal:1', 'weight' => 'decimal:2',];

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
}
