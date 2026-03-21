<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceModel extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'invoices';

    protected $fillable = [
        'code', 'patient_code', 'visit_code', 'encounter_code',
        'payment_type', 'invoice_date', 'total', 'cashier',
    ];
    protected $casts = ['invoice_date' => 'date', 'total' => 'float'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitModel::class, 'visit_code', 'code');
    }

    public function services(): HasMany
    {
        return $this->hasMany(InvoiceServiceModel::class, 'invoice_code', 'code');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(InvoiceMedicationModel::class, 'invoice_code', 'code');
    }

    /** Re-calculate total from line items and save */
    public function recalculateTotal(): void
    {
        $services = $this->services->sum('payment');
        $meds = $this->medications->sum('payment');
        $this->update(['total' => $services + $meds]);
    }
}
