<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSignObservationModel extends Model
{
    use SoftDeletes, Auditable;

    const ALLOWED_NAMES = [
        'temperature', 'heart_rate', 'respiratory_rate',
        'blood_pressure_systolic', 'blood_pressure_diastolic',
        'oxygen_saturation', 'blood_glucose', 'weight', 'height', 'bmi',
    ];

    protected $fillable = ['vital_sign_code', 'name', 'value', 'unit'];

    protected $casts = ['value' => 'float'];

    protected $table = 'vital_sign_observations';
}
