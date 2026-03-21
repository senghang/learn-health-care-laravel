<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientAddressModel extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'patient_code',
        'province_code',
        'province_name',
        'district_code',
        'district_name',
        'commune_code',
        'commune_name',
        'village_code',
        'village_name',
        'house_number',
        'street_number',
        'location'
    ];

    protected $table = 'patient_addresses';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    /** Human-readable full address string */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->house_number ? "ផ្ទះ {$this->house_number}" : null,
            $this->street_number ? "ផ្លូវ {$this->street_number}" : null,
            $this->village_name,
            $this->commune_name,
            $this->district_name,
            $this->province_name,
        ])->filter()->implode(', ');
    }
}
