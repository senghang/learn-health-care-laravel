<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WardModel extends Model
{
    use SoftDeletes, Auditable, HasTranslations, LogsActivity;

    protected $table = 'wards';

    protected array $translatable = ['name'];

    protected $fillable = [
        'clinic_id', 'code', 'name', 'name_kh', 'name_en',
        'type', 'capacity', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'capacity' => 'integer'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(RoomModel::class, 'ward_id');
    }

    public function beds(): HasMany
    {
        return $this->hasMany(BedModel::class, 'ward_id');
    }

    /** Count of occupied beds — cheap single query */
    public function occupiedCount(): int
    {
        return $this->beds()->where('status', 'occupied')->count();
    }

    /** Occupancy rate 0–100 */
    public function occupancyRate(): float
    {
        if ($this->capacity <= 0) return 0;
        return round($this->occupiedCount() / $this->capacity * 100, 1);
    }
}
