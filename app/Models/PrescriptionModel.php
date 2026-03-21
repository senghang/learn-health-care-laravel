<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'prescriptions';

    protected $fillable = ['code', 'patient_code', 'visit_code', 'encounter_code', 'prescribed_at', 'prescribed_by', 'title'];
    
    protected $casts = ['prescribed_at' => 'datetime'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(PrescriptionMedicationModel::class, 'prescription_code', 'code');
    }
}
