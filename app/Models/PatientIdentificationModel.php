<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientIdentificationModel extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'patient_code',
        'card_code',
        'card_type',
        'created_by',
        'updated_by',
    ];

    protected $table = 'patient_identifications';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }
}
