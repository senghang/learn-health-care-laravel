<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineModel extends Model
{
    use SoftDeletes, Auditable, HasTranslations, LogsActivity, HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\MedicineFactory::new();
    }

    protected $table = 'medicines';

    protected array $translatable = ['name'];

    protected $fillable = [
        'clinic_id', 'code', 'name', 'name_kh', 'name_en',
        'generic_name', 'form', 'strength', 'unit',
        'price', 'stock', 'stock_alert', 'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'stock_alert' => 'integer',
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_alert;
    }

    /** Decrement stock safely — throws if insufficient */
    public function dispense(int $qty): void
    {
        if ($this->stock < $qty) {
            throw new \RuntimeException("Insufficient stock for {$this->name}: have {$this->stock}, need {$qty}.");
        }
        $this->decrement('stock', $qty);
    }
}
