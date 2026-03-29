<?php

namespace App\Models;

use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalanceModel extends Model
{
    use ClinicScope;

    protected $table = 'stock_balances';

    protected $fillable = [
        'clinic_id', 'medicine_id',
        'quantity_on_hand', 'quantity_reserved',
        'reorder_level', 'average_cost', 'total_value',
        'last_movement_at', 'last_counted_at',
    ];

    protected $casts = [
        'quantity_on_hand'  => 'integer',
        'quantity_reserved' => 'integer',
        'average_cost'      => 'float',
        'total_value'       => 'float',
        'last_movement_at'  => 'datetime',
        'last_counted_at'   => 'datetime',
    ];

    // No SoftDeletes — balance rows are upserted, never soft-deleted

    public function medicine(): BelongsTo { return $this->belongsTo(MedicineModel::class, 'medicine_id'); }
    public function clinic(): BelongsTo { return $this->belongsTo(ClinicModel::class); }

    /**
     * Sync balance from medicines.stock (idempotent).
     */
    public static function syncFromMedicine(MedicineModel $medicine): self
    {
        return static::updateOrCreate(
            ['clinic_id' => $medicine->clinic_id, 'medicine_id' => $medicine->id],
            [
                'quantity_on_hand'  => $medicine->stock,
                'reorder_level'     => $medicine->stock_alert,
                'average_cost'      => $medicine->price,
                'total_value'       => $medicine->stock * $medicine->price,
                'last_movement_at'  => now(),
            ]
        );
    }
}
