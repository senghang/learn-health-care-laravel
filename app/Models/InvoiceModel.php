<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * InvoiceModel — now includes ClinicScope.
 * Also adds 'status' column for pending/partial/paid/void tracking.
 */
class InvoiceModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope; // ← ClinicScope ADDED

    protected $table = 'invoices';

    protected $fillable = [
        'clinic_id',
        'code',
        'patient_code',
        'visit_code',
        'encounter_code',
        'payment_type',
        'invoice_date',
        'total',
        'status',    // ← ADDED: 'pending','partial','paid','void'
        'cashier',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total'        => 'float',
    ];

    // Scopes for status filtering
    public function scopePending($query)  { return $query->where('status', 'pending');  }
    public function scopePaid($query)     { return $query->where('status', 'paid');     }

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

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentModel::class, 'invoice_code', 'code');
    }

    /** Recalculate and update total from line items */
    public function recalculateTotal(): void
    {
        $services = $this->services()->sum('payment');
        $meds     = $this->medications()->sum('payment');
        $paid     = $this->payments()->sum('amount');

        $total  = $services + $meds;
        $status = match(true) {
            $paid <= 0       => 'pending',
            $paid >= $total  => 'paid',
            default          => 'partial',
        };

        $this->update(['total' => $total, 'status' => $status]);
    }
}
