<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PatientContactModel — multiple contacts per patient.
 *
 * Relationship: Patient hasMany PatientContacts (via patient_code → patients.code)
 *
 * This is ADDITIVE — does not replace the emergency_contact_name/phone
 * columns on the patients table. Those fields remain for backward compat.
 */
class PatientContactModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'patient_contacts';

    protected $fillable = [
        'patient_code',
        'contact_name',
        'contact_phone',
        'relationship',
        'is_emergency',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_emergency' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }
}
