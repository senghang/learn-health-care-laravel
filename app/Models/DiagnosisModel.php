<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiagnosisModel extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'patient_code', 'visit_code', 'encounter_code',
        'diagnosis_type', 'diagnosis_code', 'diagnosis_name',
        'diagnosis_description', 'diagnosed_at', 'diagnosed_by', 'title',
    ];
    protected $casts = ['diagnosed_at' => 'datetime'];

    protected $table = 'diagnoses';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }
}
