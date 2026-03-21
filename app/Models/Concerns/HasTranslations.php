<?php

namespace App\Models\Concerns;

use App\Models\TranslationModel;

/**
 * HasTranslations
 *
 * Attach to any model to get bilingual field values.
 *
 * Usage on model:
 *   class WardModel extends Model {
 *       use HasTranslations;
 *       protected array $translatable = ['name', 'description'];
 *   }
 *
 * Usage in code:
 *   $ward->setTranslation('name', 'km', 'សេវាស្ត្រី');
 *   $ward->setTranslation('name', 'en', 'Maternity Ward');
 *   $ward->getTranslation('name', 'km');   // 'សេវាស្ត្រី'
 *   $ward->trans('name');                  // auto-uses app locale
 *
 * In Blade:
 *   {{ $ward->trans('name') }}
 */
trait HasTranslations
{
    // ── Relationship ──────────────────────────────────────────────────────────

    public function translations()
    {
        return $this->hasMany(TranslationModel::class, 'model_id')
                    ->where('model', static::class);
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * Get a translated field value.
     * Falls back to: requested locale → 'km' → 'en' → column value.
     */
    public function getTranslation(string $field, string $locale): ?string
    {
        if ($this->relationLoaded('translations')) {
            $t = $this->translations
                ->where('locale', $locale)
                ->where('field', $field)
                ->first();
        } else {
            $t = TranslationModel::where('model', static::class)
                ->where('model_id', $this->getKey())
                ->where('locale', $locale)
                ->where('field', $field)
                ->first();
        }

        return $t?->value;
    }

    /**
     * Get translated value using current app locale with fallback chain.
     * Trans → km → en → raw column attribute.
     */
    public function trans(string $field): ?string
    {
        $locale = app()->getLocale();

        return $this->getTranslation($field, $locale)
            ?? $this->getTranslation($field, 'km')
            ?? $this->getTranslation($field, 'en')
            ?? ($this->getAttribute($field.'_kh') ?? $this->getAttribute($field));
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    public function setTranslation(string $field, string $locale, string $value): void
    {
        TranslationModel::updateOrCreate(
            [
                'model'    => static::class,
                'model_id' => $this->getKey(),
                'locale'   => $locale,
                'field'    => $field,
            ],
            ['value' => $value]
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return all translations as ['locale' => ['field' => 'value']].
     */
    public function allTranslations(): array
    {
        return $this->translations
            ->groupBy('locale')
            ->map(fn ($group) => $group->pluck('value', 'field'))
            ->toArray();
    }
}
