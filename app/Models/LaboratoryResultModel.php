<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryResultModel extends Model
{
    use SoftDeletes, Auditable;
    
    protected $fillable = [
        'request_code', 'name', 'category', 'value', 'value_type', 'value_unit',
        'reference_range', 'interpretation', 'verified_at', 'verified_by', 'recorded_at', 'recorded_by',
    ];
    protected $casts = ['verified_at' => 'datetime', 'recorded_at' => 'datetime'];

    protected $table = 'laboratory_results';

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(LaboratoryModel::class, 'request_code', 'code');
    }
}
