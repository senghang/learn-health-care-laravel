<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope;

    protected $table = 'invoices';

    protected $fillable = [
        'clinic_id', 'code', 'patient_code', 'visit_code', 'encounter_code',
        'payment_type', 'invoice_date', 'due_date',
        'subtotal', 'discount_total', 'tax_total', 'total', 'currency',
        'status', 'cashier',
        'insurance_provider', 'claim_number', 'claim_status',
        'notes', 'print_template_code',
    ];

    protected $casts = [
        'invoice_date'   => 'date',
        'due_date'       => 'date',
        'subtotal'       => 'float',
        'discount_total' => 'float',
        'tax_total'      => 'float',
        'total'          => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function patient(): BelongsTo { return $this->belongsTo(PatientModel::class, 'patient_code', 'code'); }
    public function visit(): BelongsTo { return $this->belongsTo(VisitModel::class, 'visit_code', 'code'); }
    public function services(): HasMany { return $this->hasMany(InvoiceServiceModel::class, 'invoice_code', 'code'); }
    public function medications(): HasMany { return $this->hasMany(InvoiceMedicationModel::class, 'invoice_code', 'code'); }
    public function payments(): HasMany { return $this->hasMany(PaymentModel::class, 'invoice_code', 'code'); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getPaidAmountAttribute(): float { return $this->payments()->sum('amount'); }
    public function getBalanceAttribute(): float { return $this->total - $this->paid_amount; }
    public function isPaid(): bool { return $this->status === 'paid'; }

    public function recalculateTotal(): void
    {
        // Services: multiply by qty (COALESCE handles legacy rows where qty was not stored)
        $svcTotal = $this->services()
            ->selectRaw('COALESCE(SUM(price * GREATEST(COALESCE(qty, 1), 1)), 0) as total')
            ->value('total') ?? 0;

        $medTotal = $this->medications()
            ->selectRaw('COALESCE(SUM(price * quantity), 0) as total')
            ->value('total') ?? 0;

        $sub = (float) $svcTotal + (float) $medTotal;

        $this->update([
            'subtotal' => $sub,
            'total'    => $sub - $this->discount_total + $this->tax_total,
        ]);
    }
}
