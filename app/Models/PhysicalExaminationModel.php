<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhysicalExaminationModel extends Model
{
    use SoftDeletes, Auditable;
    
    protected $fillable = ['patient_code', 'visit_code', 'encounter_code', 'name', 'value', 'value_type', 'value_unit'];

    protected $table = 'physical_examinations';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }
}
