<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Base\ClinicScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentModel extends Model
{
    use SoftDeletes, Auditable, ClinicScope; // ← ClinicScope added

    protected $table = 'payments';

    protected $fillable = [
        'clinic_id',
        'code', 'invoice_code', 'patient_code',
        'amount', 'method', 'reference', 'note',
        'collected_by', 'paid_at',
    ];

    protected $casts = [
        'amount'  => 'float',
        'paid_at' => 'datetime',
    ];

    const METHOD_CASH   = 'CASH';
    const METHOD_HEF    = 'HEF';
    const METHOD_NSSF   = 'NSSF';
    const METHOD_CARD   = 'CARD';
    const METHOD_BAKONG = 'BAKONG';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_code', 'code');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_code', 'code');
    }
}
