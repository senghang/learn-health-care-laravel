<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

abstract class BaseTenantModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('clinic', function ($query) {
            if (app()->has('currentClinic')) {
                $query->where('clinic_id', app('currentClinic')->id);
            }
        });

        static::creating(function ($model) {
            if (app()->has('currentClinic')) {
                $model->clinic_id = app('currentClinic')->id;
            }
        });
    }
}
