<?php

namespace App\Models\Base;

use App\Models\ClinicModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClinicScope
 *
 * Reusable trait for any model that belongs to a clinic.
 * Automatically:
 *   - Adds a global scope filtering by the current clinic's ID
 *   - Sets clinic_id on creation
 *   - Provides a `clinic()` relationship
 *
 * Usage:
 *   class PatientModel extends Model {
 *       use ClinicScope;
 *   }
 *
 * Note: Do NOT apply this trait to models that intentionally span
 * multiple clinics (e.g. Province, District, Village reference tables).
 */
trait ClinicScope
{
    protected static function bootClinicScope(): void
    {
        // Auto-filter all queries to the current clinic
        static::addGlobalScope('clinic', function ($query) {
            if (app()->has('currentClinic')) {
                $query->where(
                    (new static)->getTable() . '.clinic_id',
                    app('currentClinic')->id
                );
            }
        });

        // Auto-set clinic_id on create
        static::creating(function ($model) {
            if (app()->has('currentClinic') && empty($model->clinic_id)) {
                $model->clinic_id = app('currentClinic')->id;
            }
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }
}
