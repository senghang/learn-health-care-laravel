<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InpatientMedicationModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'inpatient_medications';

    protected $fillable = [
        'clinic_id', 'code', 'visit_code', 'patient_code', 'admission_code',
        'medicine_id', 'medicine_name', 'dosage', 'route', 'frequency', 'quantity',
        'start_date', 'end_date', 'status',
        'prescribed_by', 'administered_by', 'administered_at', 'notes',
    ];

    protected $casts = [
        'start_date'      => 'datetime',
        'end_date'        => 'datetime',
        'administered_at' => 'datetime',
        'quantity'        => 'float',
    ];

    public function medicine(): BelongsTo { return $this->belongsTo(MedicineModel::class, 'medicine_id'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }

    public function isActive(): bool { return $this->status === 'active'; }
}
