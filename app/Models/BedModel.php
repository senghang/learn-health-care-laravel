<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedModel extends Model
{
    use SoftDeletes, Auditable, LogsActivity;

    protected $table = 'beds';

    protected $fillable = [
        'room_id', 'ward_id', 'code', 'name',
        'status', 'type', 'is_active',
        'current_visit_code', 'current_patient_code',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public const STATUSES = ['available', 'occupied', 'cleaning', 'reserved', 'maintenance'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(WardModel::class, 'ward_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Assign this bed to a visit.
     * Updates status + denormalised patient/visit pointers.
     */
    public function assignTo(string $visitCode, string $patientCode): void
    {
        $this->update([
            'status'               => 'occupied',
            'current_visit_code'   => $visitCode,
            'current_patient_code' => $patientCode,
        ]);
    }

    /** Release the bed and put it into cleaning. */
    public function release(): void
    {
        $this->update([
            'status'               => 'cleaning',
            'current_visit_code'   => null,
            'current_patient_code' => null,
        ]);
    }
}
