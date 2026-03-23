<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceMedicationModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'invoice_medications';

    protected $fillable = [
        'invoice_code', 'medicine_code', 'medicine_name',
        'quantity', 'price', 'payment', 'paid', 'discount', 'discount_type',
    ];

    protected $casts = ['price' => 'float', 'quantity' => 'float'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_code', 'code');
    }
}
