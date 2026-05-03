<?php

namespace App\Models;

use Database\Factories\ClinicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Request;

class ClinicModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): ClinicFactory
    {
        return ClinicFactory::new();
    }

    protected $table = 'clinics';

    protected $fillable = [
        'code', 'name', 'name_kh', 'name_en', 'tagline',
        'logo', 'subdomain',
        'phone', 'email', 'address',
        'owner_name', 'owner_number',
        'start_date', 'end_date',
        'support', 'plan', 'max_users',
        'default_locale', 'is_active',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ClinicSettingModel::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(WardModel::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceModel::class);
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(MedicineModel::class);
    }

    public function printTemplates(): HasMany
    {
        return $this->hasMany(PrintTemplateModel::class);
    }

    // ── Settings shortcuts ────────────────────────────────────────────────────

    public function setting(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        return ClinicSettingModel::get($this->id, $key, $default, $locale);
    }

    public function setSetting(string $key, mixed $value, ?string $locale = null): void
    {
        ClinicSettingModel::set($this->id, $key, $value, $locale);
    }

    // ── Scoped queries ────────────────────────────────────────────────────────

    public static function getRecord()
    {
        $q = self::orderBy('id', 'desc');

        if (!empty(Request::get('search'))) {
            $s = Request::get('search');
            $q->where(fn($r) => $r
                ->where('code',       'LIKE', "%{$s}%")
                ->orWhere('name',     'LIKE', "%{$s}%")
                ->orWhere('name_kh',  'LIKE', "%{$s}%")
                ->orWhere('owner_name','LIKE', "%{$s}%")
            );
        }

        $perPage = (int) (Request::get('per_page') ?: 25);
        return $q->paginate($perPage);
    }

    public static function getRecordById(int $id): ?self
    {
        return self::find($id);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Contract has not yet expired */
    public function isContractActive(): bool
    {
        if (!$this->end_date) return true;
        return $this->end_date->isFuture();
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'km' && $this->name_kh) return $this->name_kh;
        if ($locale === 'en' && $this->name_en) return $this->name_en;
        return $this->name;
    }
}
