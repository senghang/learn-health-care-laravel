<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransactionModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'clinic_id', 'medicine_id', 'medicine_code', 'medicine_name',
        'visit_code', 'invoice_code', 'patient_code',
        'type', 'quantity', 'stock_before', 'stock_after',
        'unit_price', 'total_price', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
        'unit_price'   => 'float',
        'total_price'  => 'float',
    ];

    public function medicine(): BelongsTo { return $this->belongsTo(MedicineModel::class, 'medicine_id'); }
}
