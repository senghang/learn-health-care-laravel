<?php

namespace App\Models\Base;

use Illuminate\Support\Facades\Auth;

/**
 * Auditable Trait
 *
 * Automatically sets created_by and updated_by on all model events.
 * Uses the web guard (clinic users) by default.
 *
 * All clinical models should use both ClinicScope AND Auditable:
 *   use SoftDeletes, Auditable, ClinicScope;
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::creating(function (self $model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
            if (Auth::check() && empty($model->updated_by)) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function (self $model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
