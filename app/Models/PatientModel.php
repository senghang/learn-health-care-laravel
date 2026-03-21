<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'patients';

    protected $fillable = [
        'clinic_id',
        'code',
        'surname',
        'name',
        'gender',
        'birthdate',
        'phone',
        'nationality',
        'occupation',
        'marital_status',
        'status',
        'spid',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birthdate' => 'date'
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(PatientAddressModel::class, 'patient_code', 'code');
    }

    public function identifications(): HasMany
    {
        return $this->hasMany(PatientIdentificationModel::class, 'patient_code', 'code');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(VisitModel::class, 'patient_code', 'code');
    }

    public function triages(): HasMany
    {
        return $this->hasMany(TriageModel::class, 'patient_code', 'code');
    }

    public function medicalHistories(): HasMany
    {
        return $this->hasMany(MedicalHistoryModel::class, 'patient_code', 'code');
    }

    public function physicalExaminations(): HasMany
    {
        return $this->hasMany(PhysicalExaminationModel::class, 'patient_code', 'code');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(DiagnosisModel::class, 'patient_code', 'code');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PrescriptionModel::class, 'patient_code', 'code');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'patient_code', 'code');
    }

    public function laboratories(): HasMany
    {
        return $this->hasMany(LaboratoryModel::class, 'patient_code', 'code');
    }

//    public function imageries(): HasMany
//    {
//        return $this->hasMany(Imagery::class, 'patient_code', 'code');
//    }

    // ── Computed Attributes ───────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->surname} {$this->name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }
}
