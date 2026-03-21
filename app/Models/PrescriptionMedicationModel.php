<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionMedicationModel extends Model
{
    use SoftDeletes, Auditable;
    
    protected $fillable = [
        'prescription_code', 'medication_code', 'medicine_name', 'strength',
        'form', 'method', 'unit', 'morning', 'afternoon', 'evening', 'night',
        'days', 'interval', 'note',
    ];

    protected $table = 'prescription_medications';

    protected $casts = [
        'morning' => 'float', 'afternoon' => 'float', 'evening' => 'float', 'night' => 'float',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PrescriptionModel::class, 'prescription_code', 'code');
    }

    /** Total daily dose across all time slots */
    public function getTotalDailyDoseAttribute(): float
    {
        return ($this->morning ?? 0) + ($this->afternoon ?? 0)
            + ($this->evening ?? 0) + ($this->night ?? 0);
    }

    /** Total qty for the full course */
    public function getTotalQtyAttribute(): float
    {
        return $this->total_daily_dose * ($this->days ?? 1);
    }
}
