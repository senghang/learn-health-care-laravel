<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'referrals';

    protected $fillable = [
        'clinic_id', 'code', 'visit_code', 'encounter_code',
        'direction', 'referral_number', 'referral_status',
        'facility_name', 'facility_code', 'facility_phone',
        'transportation', 'reason', 'diagnosis_summary', 'clinical_notes',
        'has_called', 'caretaker_name', 'caretaker_phone',
        'referred_by', 'referred_by_phone', 'referred_at',
        'received_by', 'received_by_phone', 'received_at',
        'medications', 'follow_up_required', 'follow_up_date',
    ];

    protected $casts = [
        'has_called'         => 'boolean',
        'follow_up_required' => 'boolean',
        'referred_at'        => 'datetime',
        'received_at'        => 'datetime',
        'follow_up_date'     => 'date',
    ];

    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }

    public const FROM = 'FROM';
    public const TO   = 'TO';

    public function isInbound(): bool { return $this->direction === self::FROM; }
    public function isOutbound(): bool { return $this->direction === self::TO; }
}
