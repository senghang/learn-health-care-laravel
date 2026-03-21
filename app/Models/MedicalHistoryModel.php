<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistoryModel extends Model
{
    protected $fillable = ['patient_code', 'visit_code', 'encounter_code', 'name', 'value'];

    protected $casts = ['value' => 'array'];

    protected $table = 'medical_histories';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }
}
