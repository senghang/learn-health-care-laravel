<?php

namespace App\Models;

use App\Models\Base\Auditable;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationModel extends Model
{
    protected $table = 'translations';

    protected $fillable = ['model', 'model_id', 'locale', 'field', 'value'];

    // No soft-delete — translations are replaced not soft-deleted
}
