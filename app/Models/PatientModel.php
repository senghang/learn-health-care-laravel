<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'patients';

    protected $fillable = [
        'clinic_id', 'code', 'surname', 'name', 'sex',
        'birthdate', 'phone', 'nationality', 'occupation',
        'marital_status', 'status', 'spid',
        'blood_type', 'emergency_contact_name', 'emergency_contact_phone',
        'photo_path', 'photos', 'disabilities', 'death_date',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'death_date' => 'datetime',
        'photos' => 'array',
        'disabilities' => 'array',
    ];

    // ── Core Relationships ────────────────────────────────────────────────────

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

    // ── Clinical (OPD) ────────────────────────────────────────────────────────

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

    public function laboratories(): HasMany
    {
        return $this->hasMany(LaboratoryModel::class, 'patient_code', 'code');
    }

    public function imageries(): HasMany
    {
        return $this->hasMany(ImageryModel::class, 'patient_code', 'code');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(ReferralModel::class, 'visit_code', 'code');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(AdmissionModel::class, 'patient_code', 'code');
    }

    // ── IPD ───────────────────────────────────────────────────────────────────

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'patient_code', 'code');
    }

    // ── Billing ───────────────────────────────────────────────────────────────

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentModel::class, 'patient_code', 'code');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->surname} {$this->name}";
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }

    public function getActiveVisitAttribute(): ?VisitModel
    {
        return $this->visits()->whereNull('discharged_at')->latest('admitted_at')->first();
    }

    public function visits(): HasMany
    {
        return $this->hasMany(VisitModel::class, 'patient_code', 'code');
    }

    public function getTotalVisitsAttribute(): int
    {
        return $this->visits()->count();
    }
}
