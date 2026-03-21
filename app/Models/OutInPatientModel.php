<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutInPatientModel extends Model
{
    use SoftDeletes, Auditable;
    
    protected $table = 'out_in_patient';

    protected $fillable = [
        'code', 'visit_code', 'visit_type', 'name',
        'service_type', 'bed', 'started_at', 'ended_at',
        'encountered_by', 'title',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function triages(): HasMany
    {
        return $this->hasMany(TriageModel::class, 'encounter_code', 'code');
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSignModel::class, 'encounter_code', 'code');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(DiagnosisModel::class, 'encounter_code', 'code');
    }

    public function soap(): HasOne
    {
        return $this->hasOne(SoapModel::class, 'encounter_code', 'code');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PrescriptionModel::class, 'encounter_code', 'code');
    }

    public function laboratories(): HasMany
    {
        return $this->hasMany(LaboratoryModel::class, 'encounter_code', 'code');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceModel::class, 'encounter_code', 'code');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isIPD(): bool
    {
        return $this->visit_type === 'IPD';
    }

    public function isOPD(): bool
    {
        return $this->visit_type === 'OPD';
    }
}
