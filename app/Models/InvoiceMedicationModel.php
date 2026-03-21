<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceMedicationModel extends Model
{
    use SoftDeletes, Auditable;
    
    protected $fillable = [
        'invoice_code', 'medicine_code', 'medicine_name', 'quantity',
        'price', 'payment', 'paid', 'discount_type', 'discount',
    ];

    protected $table = 'invoice_medications';

    protected $casts = ['quantity' => 'float', 'price' => 'float', 'payment' => 'float', 'paid' => 'float', 'discount' => 'float'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_code', 'code');
    }

    public function getBalanceAttribute(): float
    {
        return $this->payment - $this->paid;
    }
}
