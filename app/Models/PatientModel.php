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
 * PatientModel
 *
 * Column notes:
 *   name    — given name (not family name)
 *   surname — family name
 *   gender  — 'M' or 'F' (DB column)
 *
 * The registration form uses 'sex' as the field name for gender to avoid
 * clashing with HTML reserved attributes. The getSexAttribute() accessor
 * makes $patient->sex work as an alias for $patient->gender.
 */
class PatientModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'patients';

    protected $fillable = [
        'clinic_id',
        'code',
        'surname',
        'name',            // given name
        'gender',          // 'M' or 'F'
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
        'birthdate' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

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

    // ── Computed Attributes ───────────────────────────────────────────────────

    /**
     * Alias: $patient->sex → reads $patient->gender
     * Lets form views use the 'sex' field name without confusion.
     */
    public function getSexAttribute(): ?string
    {
        return $this->gender;
    }

    /** Full display name */
    public function getFullNameAttribute(): string
    {
        return "{$this->surname} {$this->name}";
    }

    /** Age in years (null if no birthdate) */
    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }

    /** Gender label in Khmer / English */
    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'M' => 'ប្រុស / Male',
            'F' => 'ស្រី / Female',
            default => '—',
        };
    }
}
