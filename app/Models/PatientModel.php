<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PatientModel — core patient record.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * ADDITIVE CHANGES:
 *   ✅ Added contacts() relationship → PatientContactModel
 *   ✅ All existing relationships preserved unchanged
 *   ✅ All existing fillable/casts preserved
 *   ✅ Added 'sex' to fillable alongside 'gender' for dual-compat
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class PatientModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'patients';

    protected $fillable = [
        'clinic_id', 'code', 'surname', 'name',
        'sex',          // actual DB column name
        'birthdate', 'phone', 'nationality', 'occupation',
        'marital_status', 'status', 'spid',
        'blood_type', 'emergency_contact_name', 'emergency_contact_phone',
        'photo_path', 'photos', 'disabilities', 'death_date',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'birthdate'    => 'date',
        'death_date'   => 'datetime',
        'photos'       => 'array',
        'disabilities' => 'array',
    ];

    // ══════════════════════════════════════════════════════════════════════════
    // CORE RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

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

    /**
     * NEW: Patient contacts (emergency contacts, next-of-kin).
     * Additive — does not replace emergency_contact_name/phone on patients table.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(PatientContactModel::class, 'patient_code', 'code');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLINICAL (OPD)
    // ══════════════════════════════════════════════════════════════════════════

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
        return $this->hasMany(ReferralModel::class, 'patient_code', 'code');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IPD
    // ══════════════════════════════════════════════════════════════════════════

    public function admissions(): HasMany
    {
        return $this->hasMany(AdmissionModel::class, 'patient_code', 'code');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BILLING
    // ══════════════════════════════════════════════════════════════════════════

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'patient_code', 'code');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentModel::class, 'patient_code', 'code');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COMPUTED ATTRIBUTES
    // ══════════════════════════════════════════════════════════════════════════

    public function getFullNameAttribute(): string
    {
        return "{$this->surname} {$this->name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }

    /**
     * Backward compat: some views/services use $patient->gender.
     * The DB column is `sex` — this accessor maps gender→sex.
     */
    public function getGenderAttribute(): ?string
    {
        return $this->sex;
    }

    public function getActiveVisitAttribute(): ?VisitModel
    {
        return $this->visits()->whereNull('discharged_at')->latest('admitted_at')->first();
    }

    public function getTotalVisitsAttribute(): int
    {
        return $this->visits()->count();
    }

    /**
     * Get primary emergency contact from contacts table.
     */
    public function getEmergencyContactAttribute(): ?PatientContactModel
    {
        return $this->contacts()->where('is_emergency', true)->first();
    }
}
