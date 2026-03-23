<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionMedicationModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'prescription_medications';

    protected $fillable = [
        'prescription_code', 'medication_code', 'medicine_name',
        'strength', 'form', 'method', 'unit',
        'morning', 'afternoon', 'evening', 'night',
        'days', 'interval', 'note',
    ];

    protected $casts = [
        'morning'   => 'float',
        'afternoon' => 'float',
        'evening'   => 'float',
        'night'     => 'float',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PrescriptionModel::class, 'prescription_code', 'code');
    }

    public function getTotalDailyAttribute(): float
    {
        return ($this->morning ?? 0) + ($this->afternoon ?? 0)
             + ($this->evening ?? 0) + ($this->night ?? 0);
    }

    public function getTotalQtyAttribute(): float
    {
        return $this->total_daily * ($this->days ?? 1);
    }
}
