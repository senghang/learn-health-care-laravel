<?php
// ══════════════════════════════════════════════════════════════════════════════
// FILE: app/Models/DispenseModel.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispenseModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'dispenses';

    protected $fillable = [
        'clinic_id', 'code', 'prescription_code', 'medicine_id', 'medicine_name',
        'quantity', 'batch_no', 'expiry_date', 'patient_code', 'visit_code',
        'dispensed_by', 'dispensed_at', 'status', 'counseling_done', 'pharmacist_notes',
    ];

    protected $casts = [
        'dispensed_at'    => 'datetime',
        'expiry_date'     => 'date',
        'counseling_done' => 'boolean',
        'quantity'        => 'integer',
    ];

    public function prescription(): BelongsTo { return $this->belongsTo(PrescriptionModel::class, 'prescription_code', 'code'); }
    public function medicine(): BelongsTo { return $this->belongsTo(MedicineModel::class, 'medicine_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
}
