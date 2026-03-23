<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceServiceModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'invoice_services';

    protected $fillable = [
        'invoice_code', 'service_code', 'service_name',
        'service_category', 'price', 'qty',
    ];

    protected $casts = ['price' => 'float', 'qty' => 'float'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_code', 'code');
    }
}
