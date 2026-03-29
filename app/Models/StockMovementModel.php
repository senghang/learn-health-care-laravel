<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovementModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'stock_movements';

    protected $fillable = [
        'clinic_id', 'medicine_id', 'medicine_code', 'medicine_name',
        'type', 'quantity', 'stock_before', 'stock_after',
        'reference', 'supplier', 'unit_cost', 'expiry_date',
        'batch_no', 'note', 'recorded_by',
    ];

    protected $casts = [
        'unit_cost'    => 'float',
        'expiry_date'  => 'date',
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
    ];

    public function medicine(): BelongsTo { return $this->belongsTo(MedicineModel::class, 'medicine_id'); }
    public function clinic(): BelongsTo { return $this->belongsTo(ClinicModel::class); }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'in' => 'Stock In', 'out' => 'Stock Out', 'adjustment' => 'Adjustment',
            'expired' => 'Expired', 'return' => 'Return', default => ucfirst($type),
        };
    }

    public static function typeColor(string $type): string
    {
        return match($type) {
            'in' => '#2eca6a', 'out' => '#e74c3c', 'return' => '#ff771d', default => '#aaa',
        };
    }
}
