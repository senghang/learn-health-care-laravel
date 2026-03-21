<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralModel extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'code', 'visit_code', 'encounter_code', 'direction',
        'referral_number', 'transportation', 'reason', 'has_called',
        'caretaker_name', 'caretaker_phone',
        'referred_by', 'referred_by_phone', 'referred_at',
        'received_by', 'received_by_phone', 'received_at', 'medications',
    ];


    protected $table = 'referrals';

    protected $casts = [
        'has_called' => 'boolean',
        'referred_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function isInbound(): bool
    {
        return $this->direction === self::FROM;
    }

    public function isOutbound(): bool
    {
        return $this->direction === self::TO;
    }
}
