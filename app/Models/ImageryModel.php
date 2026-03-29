<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageryModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'imageries';

    protected $fillable = [
        'clinic_id', 'code', 'patient_code', 'visit_code', 'encounter_code',
        'category', 'status', 'urgency',
        'requested_at', 'requested_by', 'title',
        'collected_at', 'collected_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
    public function results(): HasMany { return $this->hasMany(ImageryResultModel::class, 'request_code', 'code'); }
}
