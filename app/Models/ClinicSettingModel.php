<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicSettingModel extends Model
{
    protected $table = 'clinic_settings';

    protected $fillable = ['clinic_id', 'key', 'value', 'locale'];

    protected $casts = ['value' => 'json'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    /**
     * Get a setting value for a clinic.
     * Returns $default if not found.
     */
    public static function get(int $clinicId, string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $q = static::where('clinic_id', $clinicId)->where('key', $key);

        if ($locale !== null) {
            $q->where('locale', $locale);
        } else {
            $q->whereNull('locale');
        }

        return $q->value('value') ?? $default;
    }

    /**
     * Set (upsert) a setting.
     */
    public static function set(int $clinicId, string $key, mixed $value, ?string $locale = null): void
    {
        static::updateOrCreate(
            ['clinic_id' => $clinicId, 'key' => $key, 'locale' => $locale],
            ['value' => $value]
        );
    }
}
