<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PrescriptionModel — now includes ClinicScope.
 */
class PrescriptionModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope; // ← ClinicScope ADDED

    protected $table = 'prescriptions';

    protected $fillable = [
        'clinic_id',
        'code',
        'patient_code',
        'visit_code',
        'encounter_code',
        'prescribed_at',
        'prescribed_by',
        'dispensed_status',
        'dispensed_by',
        'title',
        'created_by',
        'updated_by',
    ];

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

    public function dispenses(): HasMany
    {
        return $this->hasMany(DispenseModel::class, 'prescription_code', 'code');
    }
}
