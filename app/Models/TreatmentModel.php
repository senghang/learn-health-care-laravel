<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TreatmentModel — IPD treatment/procedure orders.
 *
 * One admission has MANY treatments across days.
 * Status: ordered → in_progress → completed
 *                 → cancelled | held
 */
class TreatmentModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'treatments';

    protected $fillable = [
        'clinic_id', 'code', 'admission_code', 'visit_code', 'patient_code',
        'treatment_type', 'name', 'instructions', 'frequency', 'route', 'duration',
        'ordered_by', 'ordered_at', 'administered_by', 'administered_at',
        'status', 'notes', 'cancellation_reason',
    ];

    protected $casts = [
        'ordered_at'      => 'datetime',
        'administered_at' => 'datetime',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionModel::class, 'admission_code', 'code');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }
}
