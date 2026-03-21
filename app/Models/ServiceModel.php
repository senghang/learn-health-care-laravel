<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceModel extends Model
{
    use SoftDeletes, Auditable, HasTranslations, LogsActivity;

    protected $table = 'services';

    protected array $translatable = ['name'];

    protected $fillable = [
        'clinic_id', 'code', 'name', 'name_kh', 'name_en',
        'category', 'price', 'is_active',
    ];

    protected $casts = ['price' => 'float', 'is_active' => 'boolean'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }
}
